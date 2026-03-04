<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Employee\Employee;
use App\Domain\Employee\EmployeeQueryRepository;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ قراءة الموظفين على MySQL (Query side).
 */
final class MySQLEmployeeQueryRepository implements EmployeeQueryRepository
{
    public function all(): array
    {
        $rows = DB::table('employees')
            ->select('id', 'type', 'name')
            ->orderBy('id', 'asc')
            ->get();

        return $rows->map(
            static fn($r) => new Employee((int) $r->id, (string) $r->type, (string) $r->name)
        )->all();
    }
}

