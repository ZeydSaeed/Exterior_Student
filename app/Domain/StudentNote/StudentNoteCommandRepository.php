<?php

namespace App\Domain\StudentNote;

/**
 * واجهة كتابة ملاحظات الطالب (CQRS — Command).
 */
interface StudentNoteCommandRepository
{
    public function create(int $studentId, string $body): void;

    public function update(int $studentId, int $noteId, string $body): void;

    public function delete(int $studentId, int $noteId): void;
}
