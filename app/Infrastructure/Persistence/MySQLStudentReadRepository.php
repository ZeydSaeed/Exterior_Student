<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\StudentCertificate;
use App\Domain\Student\StudentReadRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تنفيذ قراءة طالب واحد لصفحة التأييد (CQRS — Read).
 * يدعم الجداول المُطبّعة عند وجودها، وإلا main_table.
 */
final class MySQLStudentReadRepository implements StudentReadRepository
{
    public function findById(int $id): ?StudentCertificate
    {
        if (Schema::hasTable('students')) {
            $row = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
                ->where('s.id', $id)
                ->selectRaw('s.exam_number, p.first_name, p.father_name, p.grandfather_name, p.surname, p.gender, p.birth_date, b.name_ar AS branch, m.name_ar AS specialization, y.year_label AS academic_year, rt.name_ar AS result, a.round')
                ->first();
            if (! $row) {
                return null;
            }
            $birthDate = $row->birth_date ? (is_string($row->birth_date) ? $row->birth_date : $row->birth_date->format('Y-m-d')) : '';
            return new StudentCertificate(
                firstName: trim((string) ($row->first_name ?? '')),
                fatherName: trim((string) ($row->father_name ?? '')),
                grandName: trim((string) ($row->grandfather_name ?? '')),
                lastName: trim((string) ($row->surname ?? '')),
                examNumberValue: trim((string) ($row->exam_number ?? '')),
                birthDate: $birthDate,
                branch: trim((string) ($row->branch ?? '')),
                specialization: trim((string) ($row->specialization ?? '')),
                academicYear: trim((string) ($row->academic_year ?? '')),
                result: trim((string) ($row->result ?? '')),
                round: trim((string) ($row->round ?? '')),
                gender: trim((string) ($row->gender ?? '')),
            );
        }
        $row = DB::table('main_table')->where('id', $id)->first();
        if (! $row) {
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
