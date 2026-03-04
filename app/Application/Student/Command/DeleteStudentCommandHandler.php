<?php

namespace App\Application\Student\Command;

use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;
use DomainException;

/**
 * أمر حذف طالب واحد من النظام.
 */
final class DeleteStudentCommandHandler
{
    public function __construct(
        private StudentCommandRepository $repository,
        private StudentQueryRepository $queryRepository,
    ) {}

    public function handle(int $id): bool
    {
        $student = $this->queryRepository->getStudentById($id);
        if ($student === null) {
            return false;
        }

        // تطبيق قواعد الدومين قبل الحذف
        $student->ensureCanBeDeleted();

        return $this->repository->deleteStudent($id);
    }
}

