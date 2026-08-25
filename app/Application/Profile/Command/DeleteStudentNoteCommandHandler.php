<?php

namespace App\Application\Profile\Command;

use App\Domain\StudentNote\StudentNoteCommandRepository;

/**
 * أمر حذف ملاحظة من السجل الشخصي للطالب.
 */
final class DeleteStudentNoteCommandHandler
{
    public function __construct(
        private StudentNoteCommandRepository $repository
    ) {}

    public function handle(int $studentId, int $noteId): void
    {
        $this->repository->delete($studentId, $noteId);
    }
}
