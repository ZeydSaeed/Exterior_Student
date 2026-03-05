<?php

namespace App\Application\Student\DTO;

/**
 * DTO لعرض/تعديل درجات الطالب (استجابة API المودال)
 */
final class StudentGradesDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $full_name,
        public readonly string $name_student,
        public readonly string $name_father,
        public readonly string $name_grandfather,
        public readonly string $name_surname,
        public readonly string $exam_number,
        public readonly string $gender,
        public readonly string $branch,
        public readonly string $major,
        public readonly string $academic_year,
        public readonly string $result,
        /** @var array<int, array{subject: string, score: string}> */
        public readonly array $grades,
        public readonly string $total,
        public readonly string $average,
        public readonly string $round,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'name_student' => $this->name_student,
            'name_father' => $this->name_father,
            'name_grandfather' => $this->name_grandfather,
            'name_surname' => $this->name_surname,
            'exam_number' => $this->exam_number,
            'gender' => $this->gender,
            'branch' => $this->branch,
            'major' => $this->major,
            'academic_year' => $this->academic_year,
            'result' => $this->result,
            'grades' => $this->grades,
            'total' => $this->total,
            'average' => $this->average,
            'round' => $this->round,
        ];
    }
}
