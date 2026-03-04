<?php

namespace App\Application\Employee\Query;

use App\Application\Employee\DTO\EmployeeDTO;
use App\Application\Employee\DTO\ListEmployeesResponseDTO;
use App\Domain\Employee\EmployeeQueryRepository;
use App\Domain\Employee\EmployeeType;

/**
 * معالج استعلام قائمة الموظفين (CQRS — Query Handler).
 */
final class ListEmployeesQueryHandler
{
    public function __construct(
        private EmployeeQueryRepository $repository
    ) {}

    public function handle(): ListEmployeesResponseDTO
    {
        $employees = $this->repository->all();

        $dtoEmployees = array_map(
            static fn($e) => new EmployeeDTO($e->id, $e->type, $e->name),
            $employees
        );

        return new ListEmployeesResponseDTO(
            employees: $dtoEmployees,
            types: EmployeeType::all(),
        );
    }
}

