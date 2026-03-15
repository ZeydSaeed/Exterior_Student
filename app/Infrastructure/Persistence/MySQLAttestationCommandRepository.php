<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Attestation\AttestationCommandRepository;
use Illuminate\Support\Facades\DB;

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
            DB::table('certificate')->insert([
                'student_id' => $studentId,
                'exam_number' => $examNumber,
                'type' => $type,
                'date' => $date ?: null,
                'number' => $number,
                'issued_to' => $issuedTo,
                'right_title' => $rightTitle,
                'right_employee_name' => $rightEmployeeName,
                'left_title' => $leftTitle,
                'left_employee_name' => $leftEmployeeName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
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
                'right_employee_name' => $rightEmployeeName,
                'left_title' => $leftTitle,
                'left_employee_name' => $leftEmployeeName,
                'updated_at' => now(),
            ]);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            DB::table('certificate')->where('id', $id)->delete();
        });
    }
}
