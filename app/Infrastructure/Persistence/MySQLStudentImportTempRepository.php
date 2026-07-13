<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\StudentImportTempRepository;
use App\Support\ImportDateNormalizer;
use Illuminate\Support\Facades\DB;

final class MySQLStudentImportTempRepository implements StudentImportTempRepository
{
    public function insertBatch(string $batchId, array $rows): void
    {
        if (empty($rows)) {
            return;
        }
        $now = now();
        $inserts = [];
        foreach ($rows as $row) {
            $inserts[] = [
                'import_batch_id' => $batchId,
                'row_index' => $row['row_index'],
                'exam_number' => $row['exam_number'] ?? null,
                'first_name' => $row['first_name'] ?? null,
                'father' => $row['father'] ?? null,
                'grandfather' => $row['grandfather'] ?? null,
                'last_name' => $row['last_name'] ?? null,
                'mother' => $row['mother'] ?? null,
                'birth_date' => $this->normalizeDate($row['birth_date'] ?? null),
                'birth_place' => $row['birth_place'] ?? null,
                'gender' => $row['gender'] ?? null,
                'branch' => $row['branch'] ?? null,
                'major' => $row['major'] ?? null,
                'academic_year' => $row['academic_year'] ?? null,
                'last_school' => $row['last_school'] ?? null,
                'document_number' => $row['document_number'] ?? null,
                'document_date' => $this->normalizeDate($row['document_date'] ?? null),
                'issue_place' => $row['issue_place'] ?? null,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('students_import_temp')->insert($inserts);
    }

    public function getByBatchId(string $batchId): array
    {
        $rows = DB::table('students_import_temp')
            ->where('import_batch_id', $batchId)
            ->orderBy('row_index')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[] = (object) [
                'id' => (int) $row->id,
                'row_index' => (int) $row->row_index,
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
                'status' => $row->status,
                'error' => $row->error,
            ];
        }

        return $out;
    }

    public function updateRowStatus(int $id, string $status, ?string $error = null): void
    {
        DB::table('students_import_temp')->where('id', $id)->update([
            'status' => $status,
            'error' => $error,
            'updated_at' => now(),
        ]);
    }

    public function deleteByBatchId(string $batchId): void
    {
        DB::table('students_import_temp')->where('import_batch_id', $batchId)->delete();
    }

    private function normalizeDate(mixed $value): ?string
    {
        return ImportDateNormalizer::toYmd($value);
    }
}
