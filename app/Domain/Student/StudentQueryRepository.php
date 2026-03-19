<?php

namespace App\Domain\Student;

/**
 * واجهة قراءة الطلاب (CQRS — Query side)
 */
interface StudentQueryRepository
{
    /**
     * @param  array{branch?: string, major?: string, gender?: string, year?: string, search?: string}  $filters
     */
    public function listWithFilters(array $filters): StudentListProjection;

    /**
     * معرفات الطلاب المطابقة للفلاتر (لطباعة القيود دفعة واحدة، بدون تقسيم صفحات).
     *
     * @param  array{branch?: string, major?: string, gender?: string, year?: string, search?: string}  $filters
     * @return list<int>
     */
    public function listIdsWithFilters(array $filters): array;

    /**
     * معرفات الطلاب الراسبين/المعيدين المطابقة للفلاتر (لحذف جماعي).
     *
     * @param  array{branch?: string, major?: string, gender?: string, year?: string, search?: string}  $filters
     * @return list<int>
     */
    public function listFailedIdsWithFilters(array $filters): array;

    /** بيانات درجات طالب واحد (للمودال) — يُرجع null إن لم يُوجَد */
    public function getGradesById(int $id): ?StudentGradesView;

    /** جلب كيان طالب واحد (لاستخدام قواعد الدومين مثل الحذف) */
    public function getStudentById(int $id): ?Student;

    /** هل يوجد طالب بالرقم الامتحاني (لاستخدامه في التحقق من التكرار عند الاستيراد) */
    public function existsExamNumber(string $examNumber): bool;

    /** بيانات طالب كاملة لصفحة سجل القيد (قيد الطالب) */
    public function getStudentDocumentInfo(int $id): ?StudentDocumentInfo;

    /**
     * قائمة الأعوام الدراسية لاستخدامها في نموذج إضافة طالب (من main_table + السنة الحالية).
     *
     * @return list<string>
     */
    public function getAcademicYearsForForm(): array;
}
