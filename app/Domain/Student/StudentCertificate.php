<?php

namespace App\Domain\Student;

/**
 * نموذج قراءة الطالب لصفحة التأييد (Certificate) — Domain Read Model
 * مستقل عن الإطار، لا يحتوي على كود Laravel.
 */
final class StudentCertificate
{
    public function __construct(
        private string $firstName,
        private string $fatherName,
        private string $grandName,
        private string $lastName,
        private string $examNumberValue,
        private string $birthDate,
        private string $branch,
        private string $specialization,
        private string $academicYear,
        private string $result,
        private string $round,
        private string $average,
        private string $gender,
    ) {}

    public function fullName(): string
    {
        return trim("{$this->firstName} {$this->fatherName} {$this->grandName} {$this->lastName}");
    }

    public function examNumber(): string
    {
        return $this->examNumberValue;
    }

    public function birthDate(): string
    {
        return $this->birthDate;
    }

    public function branch(): string
    {
        return $this->branch;
    }

    public function specialization(): string
    {
        return $this->specialization;
    }

    public function academicYear(): string
    {
        return $this->academicYear;
    }

    public function result(): string
    {
        return $this->result;
    }

    public function round(): string
    {
        return $this->round;
    }

    /**
     * المعدل كنص للعرض (من المعدل / average في قاعدة البيانات).
     */
    public function average(): string
    {
        return $this->average;
    }

    public function gender(): string
    {
        return $this->gender;
    }
}
