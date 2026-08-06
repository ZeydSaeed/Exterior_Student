<?php

namespace App\Application\Student\Import;

use App\Application\Student\Service\FirstRoundSubjectsLocker;
use App\Application\Student\Service\GradesTotalCalculator;
use App\Domain\Student\BranchMajorCatalogInterface;
use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\StudentResultsImportTempRepository;
use App\Domain\Student\SubjectCatalogInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * استيراد نتائج الطلبة من Excel: رفع => جدول مؤقت => تحقق => معاينة => ترحيل للقاعدة.
 */
final class ImportStudentResultsFromExcelUseCase
{
    public function __construct(
        private StudentResultsImportTempRepository $tempRepository,
        private StudentQueryRepository $queryRepository,
        private StudentCommandRepository $commandRepository,
        private BranchMajorCatalogInterface $branchMajorCatalog,
        private SubjectCatalogInterface $subjectCatalog,
        private FirstRoundSubjectsLocker $firstRoundSubjectsLocker,
        private GradesTotalCalculator $gradesTotalCalculator,
    ) {}

    /**
     * @return array{batch_id:string,total:int,valid:int,failed:int}
     */
    public function uploadAndStage(UploadedFile $file, string $round): array
    {
        $data = Excel::toArray(new \App\Imports\StudentsExcelImport, $file);
        $rows = $data[0] ?? [];
        if (empty($rows)) {
            return ['batch_id' => '', 'total' => 0, 'valid' => 0, 'failed' => 0];
        }
        $headerRow = array_values($rows[0]);
        $batchId = Str::uuid()->toString();
        $tempRows = [];
        $rowIndex = 1;
        foreach (array_slice($rows, 1) as $excelRow) {
            $mapped = $this->mapRow($excelRow, $headerRow, $rowIndex, $round);
            if ($mapped !== null) {
                $tempRows[] = $mapped;
            }
            $rowIndex++;
        }
        $this->tempRepository->insertBatch($batchId, $tempRows);

        $all = $this->tempRepository->getByBatchId($batchId);
        $valid = 0;
        $failed = 0;
        foreach ($all as $row) {
            [$studentId, $errors] = $this->validateRow($row);
            if ($errors === []) {
                $this->tempRepository->updateRowStatus($row->id, 'valid', null, $studentId);
                $valid++;
            } else {
                $this->tempRepository->updateRowStatus($row->id, 'failed', implode('؛ ', $errors), $studentId);
                $failed++;
            }
        }

        return ['batch_id' => $batchId, 'total' => count($all), 'valid' => $valid, 'failed' => $failed];
    }

    /**
     * @return list<object>
     */
    public function getPreview(string $batchId): array
    {
        return $this->tempRepository->getByBatchId($batchId);
    }

    /**
     * @return array{success:int,failed:int,errors:list<string>}
     */
    public function processValidRows(string $batchId): array
    {
        $rows = $this->tempRepository->getByBatchId($batchId);
        $success = 0;
        $errors = [];
        foreach ($rows as $row) {
            if ($row->status !== 'valid' || $row->student_id === null) {
                continue;
            }
            try {
                $rawScores = $this->decodeRawScores($row->subjects_json);
                $subjects = $this->buildGradesForRow(
                    (string) ($row->branch ?? ''),
                    (string) ($row->major ?? ''),
                    $rawScores
                );
                $payload = [
                    'branch' => (string) ($row->branch ?? ''),
                    'major' => (string) ($row->major ?? ''),
                    'academic_year' => (string) ($row->academic_year ?? ''),
                    'total' => (string) $this->gradesTotalCalculator->sum($subjects),
                    'average' => $this->normalizeNumericText((string) ($row->average ?? '')),
                    'result' => (string) ($row->result ?? ''),
                    'round' => (string) ($row->round ?? ''),
                    'grades' => $subjects,
                ];
                $ok = $this->commandRepository->updateGrades((int) $row->student_id, $payload);
                if ($ok) {
                    $this->firstRoundSubjectsLocker->lock((int) $row->student_id);
                    $success++;
                } else {
                    $errors[] = "صف {$row->row_index}: تعذر تحديث نتيجة الطالب";
                }
            } catch (\Throwable $e) {
                $errors[] = "صف {$row->row_index}: ".$e->getMessage();
            }
        }
        $validRows = array_filter($rows, fn ($r) => $r->status === 'valid');
        $this->tempRepository->deleteByBatchId($batchId);

        return ['success' => $success, 'failed' => count($validRows) - $success, 'errors' => $errors];
    }

    /**
     * @param  array<int,mixed>  $excelRow
     * @param  array<int,mixed>  $headerRow
     * @return array{row_index:int,exam_number:?string,student_name:?string,branch:?string,major:?string,academic_year:?string,subjects_json:?string,total:?string,average:?string,result:?string,round:?string}|null
     */
    private function mapRow(array $excelRow, array $headerRow, int $rowIndex, string $round): ?array
    {
        $get = static fn (int $i) => array_key_exists($i, $excelRow) ? trim((string) $excelRow[$i]) : '';
        $exam = $get(0);
        $name = $get(1);
        if ($exam === '') {
            return null;
        }
        $rawScores = [];
        for ($i = 5; $i <= 12; $i++) {
            $subjectName = isset($headerRow[$i]) ? trim((string) $headerRow[$i]) : '';
            $score = $get($i);
            $rawScores[] = ['idx' => $i, 'subject' => $subjectName, 'score' => $score];
        }

        return [
            'row_index' => $rowIndex,
            'exam_number' => $exam !== '' ? $exam : null,
            'student_name' => $name !== '' ? $name : null,
            'branch' => ($b = $get(2)) !== '' ? $b : null,
            'major' => ($m = $get(3)) !== '' ? $m : null,
            'academic_year' => ($y = $get(4)) !== '' ? $y : null,
            'subjects_json' => $rawScores !== [] ? json_encode($rawScores, JSON_UNESCAPED_UNICODE) : null,
            'total' => ($t = $get(13)) !== '' ? $t : null,
            'average' => ($a = $get(14)) !== '' ? $a : null,
            'result' => ($r = $get(15)) !== '' ? $r : null,
            'round' => $round !== '' ? $round : null,
        ];
    }

    /**
     * @return array{0:?int,1:list<string>}
     */
    private function validateRow(object $row): array
    {
        $errors = [];
        $exam = trim((string) ($row->exam_number ?? ''));
        $branch = trim((string) ($row->branch ?? ''));
        $major = trim((string) ($row->major ?? ''));
        $year = trim((string) ($row->academic_year ?? ''));
        if ($exam === '') {
            $errors[] = 'الرقم الامتحاني مطلوب';
        }
        if ($branch === '' || $major === '') {
            $errors[] = 'الفرع والاختصاص مطلوبان';
        }
        if ($year === '') {
            $errors[] = 'العام الدراسي مطلوب';
        }
        if ($branch !== '' && $major !== '' && ! $this->branchMajorCatalog->majorBelongsToBranch($major, $branch)) {
            $errors[] = 'الاختصاص لا يتبع الفرع';
        }

        $student = $exam !== '' ? $this->queryRepository->findByExamNumber($exam) : null;
        $studentId = null;
        if ($student === null) {
            $errors[] = 'الرقم الامتحاني غير موجود';
        } else {
            $studentId = (int) $student->id;
            if (trim((string) $student->branch) !== $branch) {
                $errors[] = 'الفرع لا يطابق بيانات الطالب';
            }
            if (trim((string) $student->major) !== $major) {
                $errors[] = 'الاختصاص لا يطابق بيانات الطالب';
            }
            if (trim((string) $student->academic_year) !== $year) {
                $errors[] = 'العام الدراسي لا يطابق بيانات الطالب';
            }
        }

        $rawScores = $this->decodeRawScores($row->subjects_json);
        $catalogSubjects = $this->subjectCatalog->getSubjectsFor($branch, $major);
        if ($catalogSubjects === []) {
            $errors[] = 'لا توجد مواد معرفة لهذا الفرع/الاختصاص';
        }
        if (count($catalogSubjects) > count($rawScores)) {
            $errors[] = 'عدد درجات الأعمدة أقل من عدد مواد الاختصاص';
        }

        return [$studentId, array_values(array_unique($errors))];
    }

    /**
     * @return list<array{idx:int,subject:string,score:string}>
     */
    private function decodeRawScores(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }
        $arr = json_decode($json, true);
        if (! is_array($arr)) {
            return [];
        }
        $out = [];
        foreach ($arr as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = [
                'idx' => (int) ($row['idx'] ?? 0),
                'subject' => trim((string) ($row['subject'] ?? '')),
                'score' => trim((string) ($row['score'] ?? '')),
            ];
        }

        return $out;
    }

    /**
     * بناء درجات المواد اعتماداً على مواد الاختصاص في النظام، مع أخذ الدرجات من أعمدة Excel (6-13) ترتيباً.
     *
     * @param  list<array{idx:int,subject:string,score:string}>  $rawScores
     * @return list<array{subject:string,score:string}>
     */
    private function buildGradesForRow(string $branch, string $major, array $rawScores): array
    {
        $catalogSubjects = $this->subjectCatalog->getSubjectsFor($branch, $major);
        if ($catalogSubjects === []) {
            return [];
        }
        usort($rawScores, static fn ($a, $b) => ($a['idx'] <=> $b['idx']));
        $grades = [];
        foreach ($catalogSubjects as $i => $subjectName) {
            $score = $this->normalizeNumericText((string) ($rawScores[$i]['score'] ?? ''));
            $grades[] = [
                'subject' => trim((string) $subjectName),
                'score' => $score,
            ];
        }

        return $grades;
    }

    /**
     * تطبيع النصوص الرقمية القادمة من Excel (يدعم الأرقام العربية والفواصل العربية).
     */
    private function normalizeNumericText(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $value = strtr($value, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            '٫' => '.',
            '٬' => '',
            ',' => '.',
        ]);

        return trim($value);
    }
}
