<?php

namespace App\Domain\Student;

/**
 * بيانات طالب كاملة لعرض صفحة قيد الطالب (سجل القيد) — قراءة فقط.
 */
final readonly class StudentDocumentInfo
{
    public function __construct(
        public string $fullName,
        public string $examNumber,
        public string $birthDate,
        public string $birthPlace,
        public string $motherName,
        public string $branch,
        public string $specialization,
        public string $lastSchool,
        public string $middleDocNumber,
        public string $middleDocDate,
        public string $issuingAuthority,
        public string $academicYear,
        public string $result,
        public string $round,
        public string $gender,
        public string $pageNumber,
        public string $enrollmentNumber,
    ) {}
}
