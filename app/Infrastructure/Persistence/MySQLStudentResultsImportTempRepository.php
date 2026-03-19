<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\StudentResultsImportTempRepository;
use Illuminate\Support\Facades\DB;

final class MySQLStudentResultsImportTempRepository implements StudentResultsImportTempRepository
{
    public function insertBatch(string $batchId, array $rows): void
    {
        if ($rows === []) {
            return;
        }
        $now = now();
        $inserts = [];
        foreach ($rows as $row) {
            $inserts[] = [
                'import_batch_id' => $batchId,
                'row_index' => $row['row_index'],
                'exam_number' => $row['exam_number'] ?? null,
                'student_name' => $row['student_name'] ?? null,
                'branch' => $row['branch'] ?? null,
                'major' => $row['major'] ?? null,
                'academic_year' => $row['academic_year'] ?? null,
                'subjects_json' => $row['subjects_json'] ?? null,
                'total' => $row['total'] ?? null,
                'average' => $row['average'] ?? null,
                'result' => $row['result'] ?? null,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('student_results_import_temp')->insert($inserts);
    }

    public function getByBatchId(string $batchId): array
    {
        $rows = DB::table('student_results_import_temp')
            ->where('import_batch_id', $batchId)
            ->orderBy('row_index')
            ->get();
        $out = [];
        foreach ($rows as $row) {
            $out[] = (object) [
                'id' => (int) $row->id,
                'row_index' => (int) $row->row_index,
                'student_id' => $row->student_id !== null ? (int) $row->student_id : null,
                'exam_number' => $row->exam_number,
                'student_name' => $row->student_name,
                'branch' => $row->branch,
                'major' => $row->major,
                'academic_year' => $row->academic_year,
                'subjects_json' => $row->subjects_json,
                'total' => $row->total,
                'average' => $row->average,
                'result' => $row->result,
                'status' => $row->status,
                'error' => $row->error,
            ];
        }
        return $out;
    }

    public function updateRowStatus(int $id, string $status, ?string $error = null, ?int $studentId = null): void
    {
        $payload = [
            'status' => $status,
            'error' => $error,
            'updated_at' => now(),
        ];
        if ($studentId !== null) {
            $payload['student_id'] = $studentId;
        }
        DB::table('student_results_import_temp')->where('id', $id)->update($payload);
    }

    public function deleteByBatchId(string $batchId): void
    {
        DB::table('student_results_import_temp')->where('import_batch_id', $batchId)->delete();
    }
}
