<?php

namespace App\Application\Student\Import;

use App\Application\Student\Command\CreateStudentCommandHandler;
use App\Domain\Student\StudentImportTempRepository;
use App\Support\ImportDateNormalizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * سيناريو استيراد الطلاب من Excel: رفع → جدول مؤقت → تحقق → معاينة → إدراج الصفوف الصالحة.
 */
final class ImportStudentsFromExcelUseCase
{
    /**
     * عناوين الأعمدة المتوقعة في Excel (الاسم الأساسي + أسماء بديلة شائعة).
     *
     * @var array<string, list<string>>
     */
    private const HEADERS = [
        'exam_number' => ['الرقم الامتحاني'],
        'first_name' => ['اسم الطالب'],
        'father' => ['اسم الاب'],
        'grandfather' => ['اسم الجد'],
        'last_name' => ['اللقب'],
        'gender' => ['الجنس'],
        'birth_date' => ['التولد', 'تاريخ التولد', 'تاريخ الولادة'],
        'birth_place' => ['محل الولادة', 'مكان الولادة', 'محل الولاده', 'مكان الولاده'],
        'mother' => ['اسم الام الكامل', 'اسم الام'],
        'branch' => ['الفرع'],
        'major' => ['الاختصاص'],
        'academic_year' => ['العام الدراسي'],
        'last_school' => ['اخر مدرسة', 'اخر مدرسة كان فيها الطالب', 'آخر مدرسة', 'آخر مدرسة كان فيها الطالب'],
        'document_number' => ['رقم الوثيقة', 'رقم الوثيقة المتوسطة'],
        'document_date' => ['تاريخها', 'تاريخ الوثيقة'],
        'issue_place' => ['جهة الاصدار', 'جهة الإصدار'],
    ];

    public function __construct(
        private StudentImportTempRepository $tempRepository,
        private StudentImportRowValidator $validator,
        private CreateStudentCommandHandler $createStudentHandler
    ) {}

    /**
     * رفع الملف وقراءته وإدراجه في الجدول المؤقت ثم التحقق من كل صف.
     *
     * @return array{batch_id: string, total: int, valid: int, failed: int}
     */
    public function uploadAndStage(UploadedFile $file): array
    {
        $data = Excel::toArray(new \App\Imports\StudentsExcelImport, $file);
        $rows = $data[0] ?? [];
        if (empty($rows)) {
            return [
                'batch_id' => '',
                'total' => 0,
                'valid' => 0,
                'failed' => 0,
            ];
        }
        $headerRow = array_values($rows[0]);
        $indexMap = $this->buildHeaderIndexMap($headerRow);
        $batchId = Str::uuid()->toString();
        $tempRows = [];
        $rowIndex = 1;
        foreach (array_slice($rows, 1) as $excelRow) {
            $row = $this->mapRowToTemp(array_values($excelRow), $indexMap, $rowIndex);
            if ($row !== null) {
                $tempRows[] = $row;
            }
            $rowIndex++;
        }
        $this->tempRepository->insertBatch($batchId, $tempRows);
        $allTemp = $this->tempRepository->getByBatchId($batchId);
        $examNumbersInFile = [];
        foreach ($allTemp as $tempRow) {
            $en = trim((string) ($tempRow->exam_number ?? ''));
            if ($en !== '') {
                $examNumbersInFile[$en] = ($examNumbersInFile[$en] ?? 0) + 1;
            }
        }
        $valid = 0;
        $failed = 0;
        foreach ($allTemp as $tempRow) {
            $arr = $this->tempRowToArray($tempRow);
            $errors = $this->validator->validate($arr);
            $examNum = trim((string) ($tempRow->exam_number ?? ''));
            if ($examNum !== '' && ($examNumbersInFile[$examNum] ?? 0) > 1) {
                $errors[] = 'الرقم الامتحاني مكرر في الملف';
            }
            if ($errors === []) {
                $this->tempRepository->updateRowStatus($tempRow->id, 'valid');
                $valid++;
            } else {
                $this->tempRepository->updateRowStatus($tempRow->id, 'failed', implode('؛ ', $errors));
                $failed++;
            }
        }

        return [
            'batch_id' => $batchId,
            'total' => count($allTemp),
            'valid' => $valid,
            'failed' => $failed,
        ];
    }

    /**
     * جلب معاينة دفعة (صفوف مع الحالة والخطأ).
     *
     * @return list<object{id: int, row_index: int, exam_number: ?string, first_name: ?string, status: string, error: ?string, ...}>
     */
    public function getPreview(string $batchId): array
    {
        return $this->tempRepository->getByBatchId($batchId);
    }

    /**
     * إدراج الصفوف الصالحة فقط في جداول الطلاب ثم حذف الدفعة من الجدول المؤقت.
     *
     * @return array{success: int, failed: int, errors: list<string>}
     */
    public function processValidRows(string $batchId): array
    {
        $rows = $this->tempRepository->getByBatchId($batchId);
        $validRows = array_filter($rows, fn ($r) => $r->status === 'valid');
        $success = 0;
        $errors = [];
        foreach ($validRows as $tempRow) {
            try {
                $data = $this->mapTempRowToCreateData($tempRow);
                $this->createStudentHandler->handle($data);
                $success++;
            } catch (\Throwable $e) {
                $errors[] = "صف {$tempRow->row_index}: ".$e->getMessage();
            }
        }
        $this->tempRepository->deleteByBatchId($batchId);

        return [
            'success' => $success,
            'failed' => count($validRows) - $success,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<int, mixed>  $headerRow
     * @return array<string, int>
     */
    private function buildHeaderIndexMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $i => $cell) {
            $normalized = $this->normalizeHeader((string) $cell);
            if ($normalized === '') {
                continue;
            }
            foreach (self::HEADERS as $key => $aliases) {
                if (isset($map[$key])) {
                    continue;
                }
                foreach ($aliases as $alias) {
                    if ($this->normalizeHeader($alias) === $normalized) {
                        $map[$key] = $i;
                        break 2;
                    }
                }
                // مطابقة مرنة لمحل الولادة عند اختلاف بسيط في العنوان
                if ($key === 'birth_place' && $this->looksLikeBirthPlaceHeader($normalized)) {
                    $map[$key] = $i;
                    break;
                }
            }
        }

        // إذا وُجد التولد واسم الام ولم يُطابق عنوان محل الولادة، خذ العمود بينهما
        if (! isset($map['birth_place']) && isset($map['birth_date'], $map['mother'])) {
            $between = $map['birth_date'] + 1;
            if ($map['mother'] === $between + 1) {
                $map['birth_place'] = $between;
            }
        }

        return $map;
    }

    private function looksLikeBirthPlaceHeader(string $normalized): bool
    {
        return str_contains($normalized, 'ولاد')
            && (str_contains($normalized, 'محل') || str_contains($normalized, 'مكان'));
    }

    private function normalizeHeader(string $s): string
    {
        $s = preg_replace('/[\x{FEFF}\x{200B}\x{200C}\x{200D}\x{2060}]/u', '', $s) ?? $s;
        $s = str_replace(["\xc2\xa0", "\xC2\xA0"], ' ', $s);
        $s = trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
        $s = str_replace(['أ', 'إ', 'آ', 'ى', 'ة'], ['ا', 'ا', 'ا', 'ا', 'ه'], $s);
        $s = preg_replace('/[:：\-–_]+$/u', '', $s) ?? $s;

        return trim($s);
    }

    /**
     * @param  array<int, mixed>  $excelRow
     * @param  array<string, int>  $indexMap
     * @return array{row_index: int, exam_number: ?string, ...}|null
     */
    private function mapRowToTemp(array $excelRow, array $indexMap, int $rowIndex): ?array
    {
        $get = fn (string $key) => isset($indexMap[$key]) && array_key_exists($indexMap[$key], $excelRow) ? $excelRow[$indexMap[$key]] : null;
        $getStr = fn (string $key) => $get($key) !== null ? trim((string) $get($key)) : null;
        $exam = $getStr('exam_number');
        $first = $getStr('first_name');
        if (($exam === '' || $exam === null) && ($first === '' || $first === null)) {
            return null;
        }
        $birthDateRaw = $get('birth_date');
        $docDateRaw = $get('document_date');

        return [
            'row_index' => $rowIndex,
            'exam_number' => $exam ?: null,
            'first_name' => $getStr('first_name') ?: null,
            'father' => $getStr('father') ?: null,
            'grandfather' => $getStr('grandfather') ?: null,
            'last_name' => $getStr('last_name') ?: null,
            'mother' => $getStr('mother') ?: null,
            'birth_date' => $this->normalizeDateValue($birthDateRaw),
            'birth_place' => $getStr('birth_place') ?: null,
            'gender' => $getStr('gender') ?: null,
            'branch' => $getStr('branch') ?: null,
            'major' => $getStr('major') ?: null,
            'academic_year' => $getStr('academic_year') ?: null,
            'last_school' => $getStr('last_school') ?: null,
            'document_number' => $getStr('document_number') ?: null,
            'document_date' => $this->normalizeDateValue($docDateRaw),
            'issue_place' => $getStr('issue_place') ?: null,
        ];
    }

    /**
     * تحويل قيمة التاريخ من Excel إلى Y-m-d.
     * يدعم: نص (مثل 25/4/1990)، الرقم التسلسلي لـ Excel، أو كائن DateTime.
     */
    private function normalizeDateValue(mixed $value): ?string
    {
        return ImportDateNormalizer::toYmd($value);
    }

    private function tempRowToArray(object $row): array
    {
        return [
            'exam_number' => $row->exam_number,
            'first_name' => $row->first_name,
            'father' => $row->father,
            'grandfather' => $row->grandfather,
            'last_name' => $row->last_name,
            'mother' => $row->mother,
            'birth_date' => $row->birth_date,
            'birth_place' => $row->birth_place,
            'gender' => $row->gender,
            'branch' => $row->branch,
            'major' => $row->major,
            'academic_year' => $row->academic_year,
            'last_school' => $row->last_school,
            'document_number' => $row->document_number,
            'document_date' => $row->document_date,
            'issue_place' => $row->issue_place,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function mapTempRowToCreateData(object $row): array
    {
        return [
            'exam_number' => $row->exam_number,
            'name_student' => $row->first_name,
            'name_father' => $row->father,
            'name_grandfather' => $row->grandfather,
            'name_surname' => $row->last_name,
            'mother_full_name' => $row->mother,
            'birth_date' => $row->birth_date,
            'birth_place' => $row->birth_place,
            'gender' => $row->gender,
            'branch' => $row->branch,
            'major' => $row->major,
            'academic_year' => $row->academic_year,
            'last_school' => $row->last_school,
            'middle_doc_number' => $row->document_number,
            'middle_doc_date' => $row->document_date,
            'issuing_authority' => $row->issue_place,
        ];
    }
}
