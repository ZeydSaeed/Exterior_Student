<?php

namespace App\Domain\Student;

/**
 * واجهة كتابة الطلاب (CQRS — Command side)
 */
interface StudentCommandRepository
{
    /**
     * تحديث بيانات الطالب ودرجاته بالكامل (من فورم المودال)
     *
     * @param array{
     *   name_student?: string,
     *   name_father?: string,
     *   name_grandfather?: string,
     *   name_surname?: string,
     *   exam_number?: string,
     *   enrollment_number?: string,
     *   birth_date?: string,
     *   birth_place?: string,
     *   mother_full_name?: string,
     *   branch?: string,
     *   major?: string,
     *   academic_year?: string,
     *   result?: string,
     *   total?: string,
     *   average?: string,
     *   round?: string,
     *   grades?: array<int, array{subject?: string, score?: string}>
     * } $payload
     */
    public function updateGrades(int $id, array $payload): bool;

    /**
     * إدراج طالب جديد في مصدر البيانات.
     *
     * @param  array<string, string|null>  $data  الخريطة: مفتاح إنجليزي => قيمة (يشمل enrollment_number الاختياري)، يُحوّلها البنية التحتية إلى أعمدة التخزين
     * @return int معرف السجل المُدرج (id)
     */
    public function create(array $data): int;

    /**
     * حذف سجل الطالب بالكامل من مصدر البيانات.
     */
    public function deleteStudent(int $id): bool;

    /**
     * حذف عدة طلاب دفعة واحدة (لتحسين الأداء عند الحذف الجماعي).
     *
     * @param  list<int>  $ids
     * @return int عدد السجلات المحذوفة
     */
    public function deleteStudentsByIds(array $ids): int;

    /**
     * تحديث رقم الصفحة ورقم القيد لسجل قيد الطالب.
     */
    public function updateEnrollmentRecord(int $studentId, ?string $pageNumber, ?string $enrollmentNumber): void;

    /**
     * تثبيت قائمة «الدروس التي أكمل بها» من الدور الأول (تُخزَّن كـ JSON).
     *
     * @param  list<string>  $subjects
     */
    public function saveLockedSubjectsCompleted(int $studentId, array $subjects): void;
}
