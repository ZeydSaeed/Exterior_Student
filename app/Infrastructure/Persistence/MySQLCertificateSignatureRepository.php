<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Certificate\CertificateSignatureRepository;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ قراءة/كتابة إعدادات تواقيع التأييد على MySQL.
 */
final class MySQLCertificateSignatureRepository implements CertificateSignatureRepository
{
    private const TABLE = 'certificate_signatures';

    public function __construct(
        private WorkstationEmployeeCatalog $catalog
    ) {}

    public function getEmployeeIdByPosition(string $position): ?int
    {
        $this->catalog->ensure();

        $query = DB::table(self::TABLE)->where('position', $position);
        if ($this->catalog->isScoped() && CachedSchema::hasColumn(self::TABLE, 'workstation_id')) {
            $row = (clone $query)->where('workstation_id', $this->catalog->id())->first();
            if ($row === null) {
                $row = (clone $query)->whereNull('workstation_id')->first();
            }
        } else {
            $row = $query->first();
        }

        if ($row === null || $row->employee_id === null) {
            return null;
        }

        return (int) $row->employee_id;
    }

    public function setSignature(string $position, ?int $employeeId): void
    {
        $this->catalog->ensure();

        DB::transaction(function () use ($position, $employeeId): void {
            $query = DB::table(self::TABLE)->where('position', $position);
            if ($this->catalog->isScoped() && CachedSchema::hasColumn(self::TABLE, 'workstation_id')) {
                $query->where('workstation_id', $this->catalog->id());
            }

            if ((clone $query)->exists()) {
                $query->update([
                    'employee_id' => $employeeId,
                    'updated_at' => now(),
                ]);

                return;
            }

            $row = [
                'employee_id' => $employeeId,
                'position' => $position,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($this->catalog->isScoped() && CachedSchema::hasColumn(self::TABLE, 'workstation_id')) {
                $row['workstation_id'] = $this->catalog->id();
            }
            DB::table(self::TABLE)->insert($row);
        });
    }
}
