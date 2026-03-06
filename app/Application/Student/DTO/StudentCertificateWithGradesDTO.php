<?php

namespace App\Application\Student\DTO;

/**
 * DTO لعرض صفحة التأييد بالدرجات (نفس بيانات التأييد + جدول الدرجات والمجموع كتابة).
 */
final class StudentCertificateWithGradesDTO
{
    /**
     * @param array<int, array{subject: string, score: string, score_words: string}> $gradesTable صفوف جدول الدرجات
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
        public string $gender,
        public array $employees,
        public array $gradesTable,
        public string $total,
        public string $totalWords,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'full_name'       => $this->fullName,
            'exam_number'     => $this->examNumber,
            'birth_date'      => $this->birthDate,
            'branch'          => $this->branch,
            'specialization'  => $this->specialization,
            'academic_year'   => $this->academicYear,
            'result'          => $this->result,
            'round'           => $this->round,
            'gender'          => $this->gender,
            'employees'       => $this->employees,
            'grades_table'    => $this->gradesTable,
            'total'           => $this->total,
            'total_words'     => $this->totalWords,
        ];
    }
}
