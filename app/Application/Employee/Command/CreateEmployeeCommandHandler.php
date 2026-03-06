<?php

namespace App\Application\Employee\Command;

use App\Domain\Employee\EmployeeCommandRepository;
use App\Domain\Employee\Employee;

/**
 * أمر إنشاء موظف جديد.
 */
final class CreateEmployeeCommandHandler
{
    public function __construct(
        private EmployeeCommandRepository $repository
    ) {}

    public function handle(string $type, string $name, int $tableGroup = 1): void
    {
        $employee = Employee::createWithTableGroup($type, $name, $tableGroup);
        $this->repository->create($employee->type, $employee->name, $employee->tableGroup);
    }
}

