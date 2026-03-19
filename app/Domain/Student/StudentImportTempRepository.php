<?php

namespace App\Domain\Student;

/**
 * واجهة جدول الاستيراد المؤقت (staging) لاستيراد الطلاب من Excel.
 */
interface StudentImportTempRepository
{
    /**
     * إدراج صفوف مؤقتة دفعة واحدة.
     *
     * @param  string  $batchId  معرف الدفعة
     * @param  array<int, array{row_index: int, exam_number: ?string, first_name: ?string, father: ?string, grandfather: ?string, last_name: ?string, mother: ?string, birth_date: ?string, birth_place: ?string, gender: ?string, branch: ?string, major: ?string, academic_year: ?string, last_school: ?string, document_number: ?string, document_date: ?string, issue_place: ?string}>  $rows
     */
    public function insertBatch(string $batchId, array $rows): void;

    /**
     * جلب جميع صفوف دفعة معينة مرتبة حسب row_index.
     *
     * @return list<object{id: int, row_index: int, exam_number: ?string, first_name: ?string, father: ?string, grandfather: ?string, last_name: ?string, mother: ?string, birth_date: ?string, birth_place: ?string, gender: ?string, branch: ?string, major: ?string, academic_year: ?string, last_school: ?string, document_number: ?string, document_date: ?string, issue_place: ?string, status: string, error: ?string}>
     */
    public function getByBatchId(string $batchId): array;

    /**
     * تحديث حالة وخطأ صف مؤقت.
     */
    public function updateRowStatus(int $id, string $status, ?string $error = null): void;

    /**
     * حذف جميع صفوف دفعة.
     */
    public function deleteByBatchId(string $batchId): void;
}
