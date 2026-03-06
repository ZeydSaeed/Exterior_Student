<?php

namespace App\Domain\Employee;

/**
 * واجهة كتابة الموظفين (CQRS — Command side).
 */
interface EmployeeCommandRepository
{
    public function create(string $type, string $name, int $tableGroup = 1): void;

    public function update(int $id, string $type, string $name): void;

    public function delete(int $id): void;
}

