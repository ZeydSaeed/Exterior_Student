<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Employee\EmployeeCommandRepository;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ كتابة الموظفين على MySQL (Command side).
 */
final class MySQLEmployeeCommandRepository implements EmployeeCommandRepository
{
    public function __construct(
        private WorkstationEmployeeCatalog $catalog
    ) {}

    public function create(string $type, string $name, int $tableGroup = 1): void
    {
        $this->catalog->ensure();

        DB::transaction(function () use ($type, $name, $tableGroup): void {
            $row = [
                'type' => $type,
                'name' => $name,
                'table_group' => $tableGroup,
            ];
            if ($this->catalog->isScoped()) {
                $row['workstation_id'] = $this->catalog->id();
            }
            DB::table('employees')->insert($row);
        });
    }

    public function update(int $id, string $type, string $name): void
    {
        $this->catalog->ensure();

        DB::transaction(function () use ($id, $type, $name): void {
            $query = DB::table('employees')->where('id', $id);
            if ($this->catalog->isScoped()) {
                $query->where('workstation_id', $this->catalog->id());
            }
            $query->update([
                'type' => $type,
                'name' => $name,
            ]);
        });
    }

    public function delete(int $id): void
    {
        $this->catalog->ensure();

        DB::transaction(function () use ($id): void {
            $query = DB::table('employees')->where('id', $id);
            if ($this->catalog->isScoped()) {
                $query->where('workstation_id', $this->catalog->id());
            }
            $query->delete();
        });
    }
}
