<?php

namespace App\Application\Student\Import;

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
        private SubjectCatalogInterface $subjectCatalog
    ) {}

    /**
     * @return array{batch_id:string,total:int,valid:int,failed:int}
     */
    public function uploadAndStage(UploadedFile $file): array
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
            $mapped = $this->mapRow($excelRow, $headerRow, $rowIndex);
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
                $subjects = $this->decodeSubjects($row->subjects_json);
                $payload = [
                    'branch' => (string) ($row->branch ?? ''),
                    'major' => (string) ($row->major ?? ''),
                    'academic_year' => (string) ($row->academic_year ?? ''),
                    'total' => (string) ($row->total ?? ''),
                    'average' => (string) ($row->average ?? ''),
                    'result' => (string) ($row->result ?? ''),
                    'grades' => $subjects,
                ];
                $ok = $this->commandRepository->updateGrades((int) $row->student_id, $payload);
                if ($ok) {
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
     * @param array<int,mixed> $excelRow
     * @param array<int,mixed> $headerRow
     * @return array{row_index:int,exam_number:?string,student_name:?string,branch:?string,major:?string,academic_year:?string,subjects_json:?string,total:?string,average:?string,result:?string}|null
     */
    private function mapRow(array $excelRow, array $headerRow, int $rowIndex): ?array
    {
        $get = static fn (int $i) => array_key_exists($i, $excelRow) ? trim((string) $excelRow[$i]) : '';
        $exam = $get(0);
        $name = $get(1);
        if ($exam === '' && $name === '') {
            return null;
        }
        $subjects = [];
        for ($i = 5; $i <= 12; $i++) {
            $subjectName = isset($headerRow[$i]) ? trim((string) $headerRow[$i]) : '';
            $score = $get($i);
            if ($subjectName !== '') {
                $subjects[] = ['subject' => $subjectName, 'score' => $score];
            }
        }
        return [
            'row_index' => $rowIndex,
            'exam_number' => $exam !== '' ? $exam : null,
            'student_name' => $name !== '' ? $name : null,
            'branch' => ($b = $get(2)) !== '' ? $b : null,
            'major' => ($m = $get(3)) !== '' ? $m : null,
            'academic_year' => ($y = $get(4)) !== '' ? $y : null,
            'subjects_json' => $subjects !== [] ? json_encode($subjects, JSON_UNESCAPED_UNICODE) : null,
            'total' => ($t = $get(13)) !== '' ? $t : null,
            'average' => ($a = $get(14)) !== '' ? $a : null,
            'result' => ($r = $get(15)) !== '' ? $r : null,
        ];
    }

    /**
     * @return array{0:?int,1:list<string>}
     */
    private function validateRow(object $row): array
    {
        $errors = [];
        $exam = trim((string) ($row->exam_number ?? ''));
        $name = trim((string) ($row->student_name ?? ''));
        $branch = trim((string) ($row->branch ?? ''));
        $major = trim((string) ($row->major ?? ''));
        $year = trim((string) ($row->academic_year ?? ''));
        if ($exam === '') {
            $errors[] = 'الرقم الامتحاني مطلوب';
        }
        if ($name === '') {
            $errors[] = 'اسم الطالب مطلوب';
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
            if ($this->normalize($student->full_name) !== $this->normalize($name)) {
                $errors[] = 'اسم الطالب لا يطابق السجل';
            }
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

        $subjects = $this->decodeSubjects($row->subjects_json);
        $catalogSubjects = $this->subjectCatalog->getSubjectsFor($branch, $major);
        $catalogMap = array_fill_keys(array_map([$this, 'normalize'], $catalogSubjects), true);
        foreach ($subjects as $s) {
            $subject = trim((string) ($s['subject'] ?? ''));
            $score = trim((string) ($s['score'] ?? ''));
            if ($subject === '') {
                continue;
            }
            if (! isset($catalogMap[$this->normalize($subject)])) {
                $errors[] = "المادة {$subject} لا تتبع الاختصاص";
            }
            if ($score !== '' && ! is_numeric($score)) {
                $errors[] = "درجة المادة {$subject} غير رقمية";
            }
        }

        return [$studentId, array_values(array_unique($errors))];
    }

    /**
     * @return list<array{subject:string,score:string}>
     */
    private function decodeSubjects(?string $json): array
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
                'subject' => trim((string) ($row['subject'] ?? '')),
                'score' => trim((string) ($row['score'] ?? '')),
            ];
        }
        return $out;
    }

    private function normalize(string $v): string
    {
        $v = trim(preg_replace('/\s+/u', ' ', $v));
        return str_replace(['أ', 'إ', 'آ', 'ى'], 'ا', $v);
    }
}
