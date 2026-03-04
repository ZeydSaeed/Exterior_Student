<?php

namespace App\Application\Employee\Command;

use App\Domain\Employee\EmployeeCommandRepository;
use App\Domain\Employee\Employee;

/**
 * أمر تعديل بيانات موظف.
 */
final class UpdateEmployeeCommandHandler
{
    public function __construct(
        private EmployeeCommandRepository $repository
    ) {}

    public function handle(int $id, string $type, string $name): void
    {
        $employee = Employee::create($type, $name);
        $this->repository->update($id, $employee->type, $employee->name);
    }
}

