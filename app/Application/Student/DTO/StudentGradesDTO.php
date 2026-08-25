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
        public readonly string $birth_date,
        public readonly string $birth_place,
        public readonly string $mother_full_name,
        public readonly string $gender,
        public readonly string $branch,
        public readonly string $major,
        public readonly string $academic_year,
        public readonly string $last_school,
        public readonly string $middle_doc_number,
        public readonly string $middle_doc_date,
        public readonly string $issuing_authority,
        public readonly string $result,
        /** @var array<int, array{subject: string, score: string}> */
        public readonly array $grades,
        public readonly string $total,
        public readonly string $average,
        public readonly string $round,
        public readonly string $enrollment_number = '',
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
            'enrollment_number' => $this->enrollment_number,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'mother_full_name' => $this->mother_full_name,
            'gender' => $this->gender,
            'branch' => $this->branch,
            'major' => $this->major,
            'academic_year' => $this->academic_year,
            'last_school' => $this->last_school,
            'middle_doc_number' => $this->middle_doc_number,
            'middle_doc_date' => $this->middle_doc_date,
            'issuing_authority' => $this->issuing_authority,
            'result' => $this->result,
            'grades' => $this->grades,
            'total' => $this->total,
            'average' => $this->average,
            'round' => $this->round,
        ];
    }
}
