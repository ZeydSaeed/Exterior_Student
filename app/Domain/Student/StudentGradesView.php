<?php

namespace App\Domain\Student;

/**
 * نموذج قراءة درجات الطالب (Domain) — لا يعتمد على أسماء أعمدة DB
 */
final class StudentGradesView
{
    public function __construct(
        public readonly int $id,
        public readonly string $fullName,
        public readonly string $nameStudent,
        public readonly string $nameFather,
        public readonly string $nameGrandfather,
        public readonly string $nameSurname,
        public readonly string $examNumber,
        public readonly string $birthDate,
        public readonly string $birthPlace,
        public readonly string $motherFullName,
        public readonly string $gender,
        public readonly string $branch,
        public readonly string $major,
        public readonly string $academicYear,
        public readonly string $result,
        /** @var array<int, array{subject: string, score: string}> */
        public readonly array $grades,
        public readonly string $total,
        public readonly string $average,
        public readonly string $round,
    ) {}
}
