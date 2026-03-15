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
    private const MAX_ATTESTATIONS_PER_STUDENT = 500;

    public function listByStudentId(int $studentId): array
    {
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
            ->where('student_id', $studentId)
            ->orderByRaw('`date` DESC')
            ->orderBy('id', 'desc')
            ->limit(self::MAX_ATTESTATIONS_PER_STUDENT)
            ->get();

        $list = [];
        foreach ($rows as $row) {
            $list[] = Attestation::fromRow($row);
        }
        return $list;
    }
}
