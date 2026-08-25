<?php

namespace App\Application\Profile\Command;

use App\Domain\StudentNote\StudentNoteCommandRepository;

/**
 * أمر تعديل ملاحظة في السجل الشخصي للطالب.
 */
final class UpdateStudentNoteCommandHandler
{
    public function __construct(
        private StudentNoteCommandRepository $repository
    ) {}

    public function handle(int $studentId, int $noteId, string $body): void
    {
        $this->repository->update($studentId, $noteId, trim($body));
    }
}
