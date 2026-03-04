<?php

namespace App\Domain\Employee;

/**
 * واجهة قراءة الموظفين (CQRS — Query side).
 */
interface EmployeeQueryRepository
{
    /**
     * إرجاع جميع الموظفين.
     *
     * @return list<Employee>
     */
    public function all(): array;
}

