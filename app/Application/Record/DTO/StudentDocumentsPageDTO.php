<?php

namespace App\Application\Record\DTO;

/**
 * DTO لصفحة وثائق الطالب (بيانات الطالب وقائمة الوثائق).
 */
final class StudentDocumentsPageDTO
{
    /**
     * @param  list<RecordDTO>  $records
     */
    public function __construct(
        public readonly int $studentId,
        public readonly ?string $examNumber,
        public readonly ?string $studentName,
        public readonly ?string $branch,
        public readonly ?string $major,
        public readonly ?string $academicYear,
        public readonly ?string $round,
        public readonly ?string $gender,
        public readonly ?int $nextStudentId,
        public readonly array $records,
    ) {}

    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'exam_number' => $this->examNumber,
            'student_name' => $this->studentName,
            'branch' => $this->branch,
            'major' => $this->major,
            'academic_year' => $this->academicYear,
            'round' => $this->round,
            'gender' => $this->gender,
            'next_student_id' => $this->nextStudentId,
            'records' => array_map(static fn (RecordDTO $r) => $r->toArray(), $this->records),
        ];
    }
}
