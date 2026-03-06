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

    public function getEmployeeIdByPosition(string $position): ?int
    {
        $row = DB::table(self::TABLE)
            ->where('position', $position)
            ->first();

        if ($row === null || $row->employee_id === null) {
            return null;
        }

        return (int) $row->employee_id;
    }

    public function setSignature(string $position, ?int $employeeId): void
    {
        DB::transaction(function () use ($position, $employeeId): void {
            $exists = DB::table(self::TABLE)->where('position', $position)->exists();

            if ($exists) {
                DB::table(self::TABLE)
                    ->where('position', $position)
                    ->update([
                        'employee_id' => $employeeId,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table(self::TABLE)->insert([
                    'employee_id' => $employeeId,
                    'position' => $position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
