<?php

namespace App\Application\Employee\DTO;

/**
 * DTO لاستجابة صفحة إعدادات الموظفين.
 */
final class ListEmployeesResponseDTO
{
    /**
     * @param list<EmployeeDTO> $employees
     * @param list<string> $types
     */
    public function __construct(
        public readonly array $employees,
        public readonly array $types,
    ) {}

    public function toArray(): array
    {
        return [
            'employees' => array_map(
                static fn(EmployeeDTO $e) => $e->toArray(),
                $this->employees
            ),
            'types' => $this->types,
        ];
    }
}

