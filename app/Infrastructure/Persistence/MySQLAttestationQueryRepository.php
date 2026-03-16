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
        $rows = DB::table('certificate as c')
            ->leftJoin('certificate_signers as cs_right', function ($j): void {
                $j->on('cs_right.certificate_id', '=', 'c.id')->where('cs_right.position', '=', 'right');
            })
            ->leftJoin('employees as e_right', 'e_right.id', '=', 'cs_right.employee_id')
            ->leftJoin('certificate_signers as cs_left', function ($j): void {
                $j->on('cs_left.certificate_id', '=', 'c.id')->where('cs_left.position', '=', 'left');
            })
            ->leftJoin('employees as e_left', 'e_left.id', '=', 'cs_left.employee_id')
            ->where('c.student_id', $studentId)
            ->orderByRaw('c.`date` DESC')
            ->orderBy('c.id', 'desc')
            ->limit(self::MAX_ATTESTATIONS_PER_STUDENT)
            ->select([
                'c.id',
                'c.exam_number',
                'c.type',
                'c.date',
                'c.number',
                'c.issued_to',
                'c.right_title',
                DB::raw('e_right.name AS right_employee_name'),
                'c.left_title',
                DB::raw('e_left.name AS left_employee_name'),
            ])
            ->get();

        $list = [];
        foreach ($rows as $row) {
            $list[] = Attestation::fromRow($row);
        }
        return $list;
    }
}
