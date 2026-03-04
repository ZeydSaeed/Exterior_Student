<?php

namespace App\Application\Employee\Command;

use App\Domain\Employee\EmployeeCommandRepository;

/**
 * أمر حذف موظف.
 */
final class DeleteEmployeeCommandHandler
{
    public function __construct(
        private EmployeeCommandRepository $repository
    ) {}

    public function handle(int $id): void
    {
        $this->repository->delete($id);
    }
}

