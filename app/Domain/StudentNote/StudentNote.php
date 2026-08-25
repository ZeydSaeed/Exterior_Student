<?php

namespace App\Domain\StudentNote;

/**
 * كيان ملاحظة الطالب في السجل الشخصي.
 */
final class StudentNote
{
    public function __construct(
        public readonly int $id,
        public readonly int $studentId,
        public readonly string $body,
    ) {}

    public static function fromRow(object $row): self
    {
        return new self(
            id: (int) ($row->id ?? 0),
            studentId: (int) ($row->student_id ?? 0),
            body: (string) ($row->body ?? ''),
        );
    }
}
