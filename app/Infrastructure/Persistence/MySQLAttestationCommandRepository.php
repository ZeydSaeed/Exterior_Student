<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Attestation\AttestationCommandRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * كتابة التأييدات في جدول certificate (CQRS — Command).
 */
final class MySQLAttestationCommandRepository implements AttestationCommandRepository
{
    public function create(
        int $studentId,
        string $examNumber,
        string $type,
        ?string $date,
        ?string $number,
        ?string $issuedTo,
        ?string $rightTitle,
        ?string $rightEmployeeName,
        ?string $leftTitle,
        ?string $leftEmployeeName
    ): void {
        DB::transaction(function () use ($studentId, $examNumber, $type, $date, $number, $issuedTo, $rightTitle, $rightEmployeeName, $leftTitle, $leftEmployeeName): void {
            $now = now();
            $certId = DB::table('certificate')->insertGetId([
                'student_id' => $studentId,
                'exam_number' => $examNumber,
                'type' => $type,
                'date' => $date ?: null,
                'number' => $number,
                'issued_to' => $issuedTo,
                'right_title' => $rightTitle,
                'left_title' => $leftTitle,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->syncSigners((int) $certId, $rightEmployeeName, $leftEmployeeName, $rightTitle, $leftTitle);
        });
    }

    public function update(
        int $id,
        ?string $date,
        ?string $number,
        ?string $issuedTo,
        ?string $rightTitle,
        ?string $rightEmployeeName,
        ?string $leftTitle,
        ?string $leftEmployeeName
    ): void {
        DB::transaction(function () use ($id, $date, $number, $issuedTo, $rightTitle, $rightEmployeeName, $leftTitle, $leftEmployeeName): void {
            DB::table('certificate')->where('id', $id)->update([
                'date' => $date,
                'number' => $number,
                'issued_to' => $issuedTo,
                'right_title' => $rightTitle,
                'left_title' => $leftTitle,
                'updated_at' => now(),
            ]);
            $this->syncSigners($id, $rightEmployeeName, $leftEmployeeName, $rightTitle, $leftTitle);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            DB::table('certificate_signers')->where('certificate_id', $id)->delete();
            DB::table('certificate')->where('id', $id)->delete();
        });
    }

    /**
     * ربط موقّعي التأييد بالموظفين؛ إنشاء موظف بالاسم إن لم يوجد حتى تُحفظ أسماء المعدَّلة.
     */
    private function syncSigners(
        int $certificateId,
        ?string $rightEmployeeName,
        ?string $leftEmployeeName,
        ?string $rightTitle = null,
        ?string $leftTitle = null,
    ): void {
        if (! Schema::hasTable('certificate_signers')) {
            return;
        }

        DB::table('certificate_signers')->where('certificate_id', $certificateId)->delete();
        $now = now();

        $rightId = $this->resolveEmployeeId($rightEmployeeName, $rightTitle, 1);
        $leftId = $this->resolveEmployeeId($leftEmployeeName, $leftTitle, 2);

        if ($rightId !== null) {
            DB::table('certificate_signers')->insert([
                'certificate_id' => $certificateId,
                'employee_id' => $rightId,
                'position' => 'right',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($leftId !== null) {
            DB::table('certificate_signers')->insert([
                'certificate_id' => $certificateId,
                'employee_id' => $leftId,
                'position' => 'left',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function resolveEmployeeId(?string $name, ?string $type, int $tableGroup): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $type = trim((string) $type);
        if ($type === '') {
            $type = 'موظف';
        }

        $existingId = DB::table('employees')->where('name', $name)->value('id');
        if ($existingId !== null) {
            return (int) $existingId;
        }

        return (int) DB::table('employees')->insertGetId([
            'name' => $name,
            'type' => $type,
            'table_group' => $tableGroup,
        ]);
    }
}
