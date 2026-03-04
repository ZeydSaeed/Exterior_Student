<?php

namespace App\Domain\Student;

/**
 * واجهة قراءة الطلاب (CQRS — Query side)
 */
interface StudentQueryRepository
{
    /**
     * @param array{branch?: string, major?: string, gender?: string, year?: string, search?: string} $filters
     */
    public function listWithFilters(array $filters): StudentListProjection;

    /** بيانات درجات طالب واحد (للمودال) — يُرجع null إن لم يُوجَد */
    public function getGradesById(int $id): ?StudentGradesView;

    /** جلب كيان طالب واحد (لاستخدام قواعد الدومين مثل الحذف) */
    public function getStudentById(int $id): ?Student;
}
