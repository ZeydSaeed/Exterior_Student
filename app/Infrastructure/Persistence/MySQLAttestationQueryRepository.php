<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Attestation\Attestation;
use App\Domain\Attestation\AttestationQueryRepository;
use Illuminate\Support\Facades\DB;

/**
 * قراءة التأييدات من جدول certificate (CQRS — Query).
 */
final class MySQLAttestationQueryRepository implements AttestationQueryRepository
{
    public function listByStudentId(int $studentId): array
    {
        $examNumber = DB::table('main_table')
            ->where('id', $studentId)
            ->value('الرقم الامتحاني');

        if ($examNumber === null || $examNumber === '') {
            return [];
        }

        $rows = DB::table('certificate')
            ->select([
                'id',
                'exam_number',
                'type',
                'date',
                'number',
                'issued_to',
                'right_title',
                'right_employee_name',
                'left_title',
                'left_employee_name',
            ])
            ->where('exam_number', (string) $examNumber)
            ->orderByRaw('`date` DESC')
            ->orderBy('id', 'desc')
            ->get();

        $list = [];
        foreach ($rows as $row) {
            $list[] = Attestation::fromRow($row);
        }
        return $list;
    }
}
