<?php

namespace App\Domain\Student;

/**
 * واجهة جدول الاستيراد المؤقت لنتائج الطلاب من Excel.
 */
interface StudentResultsImportTempRepository
{
    /**
     * @param array<int, array{
     *   row_index:int,
     *   exam_number:?string,
     *   student_name:?string,
     *   branch:?string,
     *   major:?string,
     *   academic_year:?string,
     *   subjects_json:?string,
     *   total:?string,
     *   average:?string,
     *   result:?string,
     *   round:?string
     * }> $rows
     */
    public function insertBatch(string $batchId, array $rows): void;

    /**
     * @return list<object{id:int,row_index:int,student_id:?int,exam_number:?string,student_name:?string,branch:?string,major:?string,academic_year:?string,subjects_json:?string,total:?string,average:?string,result:?string,round:?string,status:string,error:?string}>
     */
    public function getByBatchId(string $batchId): array;

    public function updateRowStatus(int $id, string $status, ?string $error = null, ?int $studentId = null): void;

    public function deleteByBatchId(string $batchId): void;
}
