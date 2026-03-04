<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\StudentCertificate;
use App\Domain\Student\StudentReadRepository;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ قراءة طالب واحد لصفحة التأييد (CQRS — Read)
 * يعتمد على main_table فقط.
 */
final class MySQLStudentReadRepository implements StudentReadRepository
{
    public function findById(int $id): ?StudentCertificate
    {
        $row = DB::table('main_table')->where('id', $id)->first();

        if (!$row) {
            return null;
        }

        $birthDate = isset($row->{'التولد'}) ? trim((string) $row->{'التولد'}) : '';

        return new StudentCertificate(
            firstName: trim((string) ($row->{'اسم الطالب'} ?? '')),
            fatherName: trim((string) ($row->{'اسم الاب'} ?? '')),
            grandName: trim((string) ($row->{'اسم الجد'} ?? '')),
            lastName: trim((string) ($row->{'اللقب'} ?? '')),
            examNumberValue: trim((string) ($row->{'الرقم الامتحاني'} ?? '')),
            birthDate: $birthDate,
            branch: trim((string) ($row->{'الفرع'} ?? '')),
            specialization: trim((string) ($row->{'الاختصاص'} ?? '')),
            academicYear: trim((string) ($row->{'العام الدراسي'} ?? '')),
            result: trim((string) ($row->{'النتيجة'} ?? '')),
            round: trim((string) ($row->{'الدور'} ?? '')),
            gender: trim((string) ($row->{'الجنس'} ?? '')),
        );
    }
}
