<?php

namespace App\Domain\Student;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * تمثيل (Projection) لقائمة الطلاب للاستخدام داخل طبقة الـ Domain / Infrastructure.
 * طبقة Application تقوم بتحويل هذا الـ Projection إلى DTO خاص بالعرض.
 */
final class StudentListProjection
{
    public function __construct(
        public readonly LengthAwarePaginator $students,
        public readonly Collection $academicYears,
        public readonly Collection $branches,
        public readonly Collection $majors,
        public readonly Collection $genders,
        public readonly Collection $resultOptions,
        public readonly Collection $roundOptions,
    ) {}
}

