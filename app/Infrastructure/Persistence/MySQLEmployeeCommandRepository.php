<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Employee\EmployeeCommandRepository;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ كتابة الموظفين على MySQL (Command side).
 */
final class MySQLEmployeeCommandRepository implements EmployeeCommandRepository
{
    public function create(string $type, string $name): void
    {
        DB::transaction(function () use ($type, $name): void {
            DB::table('employees')->insert([
                'type' => $type,
                'name' => $name,
            ]);
        });
    }

    public function update(int $id, string $type, string $name): void
    {
        DB::transaction(function () use ($id, $type, $name): void {
            DB::table('employees')
                ->where('id', $id)
                ->update([
                    'type' => $type,
                    'name' => $name,
                ]);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            DB::table('employees')
                ->where('id', $id)
                ->delete();
        });
    }
}

