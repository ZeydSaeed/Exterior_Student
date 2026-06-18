<?php

namespace App\Application\Student\Command;

use App\Domain\Student\StudentCommandRepository;

/**
 * أمر تحديث رقم الصفحة ورقم القيد في سجل قيد الطالب.
 */
final class UpdateStudentEnrollmentRecordCommandHandler
{
    public function __construct(
        private StudentCommandRepository $repository,
    ) {}

    public function handle(int $studentId, ?string $pageNumber, ?string $enrollmentNumber): void
    {
        $this->repository->updateEnrollmentRecord($studentId, $pageNumber, $enrollmentNumber);
    }
}
