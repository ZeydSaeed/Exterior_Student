<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Workstation\WorkstationContext;
use Illuminate\Support\Facades\DB;

/**
 * ينسخ قائمة الموظفين وإعدادات التواقيع إلى الحاسبة الحالية عند أول استخدام.
 */
final class WorkstationEmployeeCatalog
{
    public function __construct(
        private WorkstationContext $workstation
    ) {}

    public function id(): string
    {
        return $this->workstation->id();
    }

    public function isScoped(): bool
    {
        return CachedSchema::hasColumn('employees', 'workstation_id')
            && $this->id() !== 'console';
    }

    public function ensure(): void
    {
        if (! $this->isScoped()) {
            return;
        }

        $workstationId = $this->id();
        if (DB::table('employees')->where('workstation_id', $workstationId)->exists()) {
            return;
        }

        $templates = DB::table('employees')
            ->whereNull('workstation_id')
            ->orderBy('id')
            ->get();

        if ($templates->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($templates, $workstationId): void {
            $idMap = [];
            foreach ($templates as $row) {
                $newId = (int) DB::table('employees')->insertGetId([
                    'type' => (string) $row->type,
                    'name' => (string) $row->name,
                    'table_group' => isset($row->table_group) ? (int) $row->table_group : 1,
                    'workstation_id' => $workstationId,
                ]);
                $idMap[(int) $row->id] = $newId;
            }

            if (! CachedSchema::hasColumn('certificate_signatures', 'workstation_id')) {
                return;
            }

            foreach (['right', 'left'] as $position) {
                $template = DB::table('certificate_signatures')
                    ->where('position', $position)
                    ->whereNull('workstation_id')
                    ->first();

                $employeeId = null;
                if ($template !== null && $template->employee_id !== null) {
                    $employeeId = $idMap[(int) $template->employee_id] ?? null;
                }

                DB::table('certificate_signatures')->insert([
                    'employee_id' => $employeeId,
                    'position' => $position,
                    'workstation_id' => $workstationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
