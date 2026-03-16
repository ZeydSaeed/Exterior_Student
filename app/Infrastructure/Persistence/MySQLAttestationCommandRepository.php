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
            $this->syncSigners((int) $certId, $rightEmployeeName, $leftEmployeeName);
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
            $this->syncSigners($id, $rightEmployeeName, $leftEmployeeName);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            DB::table('certificate_signers')->where('certificate_id', $id)->delete();
            DB::table('certificate')->where('id', $id)->delete();
        });
    }

    private function syncSigners(int $certificateId, ?string $rightEmployeeName, ?string $leftEmployeeName): void
    {
        if (! Schema::hasTable('certificate_signers')) {
            return;
        }
        DB::table('certificate_signers')->where('certificate_id', $certificateId)->delete();
        $now = now();
        $rightId = $rightEmployeeName !== null && $rightEmployeeName !== '' ? DB::table('employees')->where('name', $rightEmployeeName)->value('id') : null;
        $leftId = $leftEmployeeName !== null && $leftEmployeeName !== '' ? DB::table('employees')->where('name', $leftEmployeeName)->value('id') : null;
        if ($rightId !== null) {
            DB::table('certificate_signers')->insert(['certificate_id' => $certificateId, 'employee_id' => $rightId, 'position' => 'right', 'created_at' => $now, 'updated_at' => $now]);
        }
        if ($leftId !== null) {
            DB::table('certificate_signers')->insert(['certificate_id' => $certificateId, 'employee_id' => $leftId, 'position' => 'left', 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
