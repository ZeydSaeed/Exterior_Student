<?php

namespace App\Application\Profile\Command;

use App\Domain\StudentNote\StudentNoteCommandRepository;

/**
 * أمر إضافة ملاحظة إلى السجل الشخصي للطالب.
 */
final class CreateStudentNoteCommandHandler
{
    public function __construct(
        private StudentNoteCommandRepository $repository
    ) {}

    public function handle(int $studentId, string $body): void
    {
        $this->repository->create($studentId, trim($body));
    }
}
