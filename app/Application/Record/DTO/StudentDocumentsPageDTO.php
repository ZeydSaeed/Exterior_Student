<?php

namespace App\Application\Record\DTO;

/**
 * DTO لصفحة وثائق الطالب (الرقم الامتحاني، الاسم، قائمة الوثائق).
 */
final class StudentDocumentsPageDTO
{
    /**
     * @param list<RecordDTO> $records
     */
    public function __construct(
        public readonly int $studentId,
        public readonly ?string $examNumber,
        public readonly ?string $studentName,
        public readonly array $records,
    ) {}

    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'exam_number' => $this->examNumber,
            'student_name' => $this->studentName,
            'records' => array_map(static fn (RecordDTO $r) => $r->toArray(), $this->records),
        ];
    }
}
