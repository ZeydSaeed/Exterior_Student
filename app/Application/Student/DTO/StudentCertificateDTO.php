<?php

namespace App\Application\Student\DTO;

/**
 * DTO لعرض صفحة التأييد (Certificate)
 * البيانات من Use Case فقط، بدون منطق أعمال.
 */
final class StudentCertificateDTO
{
    /**
     * @param  array<int, array{type: string, name: string}>  $employees  الموظفون المختارون من الجلسة
     */
    public function __construct(
        public string $fullName,
        public string $examNumber,
        public string $birthDate,
        public string $branch,
        public string $specialization,
        public string $academicYear,
        public string $result,
        public string $round,
        public string $average,
        public string $gender,
        public array $employees,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'exam_number' => $this->examNumber,
            'birth_date' => $this->birthDate,
            'branch' => $this->branch,
            'specialization' => $this->specialization,
            'academic_year' => $this->academicYear,
            'result' => $this->result,
            'round' => $this->round,
            'average' => $this->average,
            'gender' => $this->gender,
            'employees' => $this->employees,
        ];
    }
}
