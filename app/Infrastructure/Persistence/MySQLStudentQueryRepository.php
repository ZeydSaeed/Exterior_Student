<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\Student;
use App\Domain\Student\StudentGradesView;
use App\Domain\Student\StudentListProjection;
use App\Domain\Student\StudentQueryRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ قراءة الطلاب على MySQL (CQRS — Query side فقط)
 */
final class MySQLStudentQueryRepository implements StudentQueryRepository
{
    private const PER_PAGE = 20;

    public function listWithFilters(array $filters): StudentListProjection
    {
        $query = DB::table('main_table')
            ->selectRaw("
                id,
                `الرقم الامتحاني` AS exam_number,
                CONCAT_WS(' ', `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`) AS full_name,
                `العام الدراسي` AS academic_year,
                `النتيجة` AS result,
                TRIM(`الفرع`) AS branch,
                TRIM(`الاختصاص`) AS major,
                TRIM(`الجنس`) AS gender
            ");

        if (!empty($filters['branch'])) {
            $query->whereRaw('TRIM(`الفرع`) = ?', [$filters['branch']]);
        }

        if (!empty($filters['major'])) {
            $query->whereRaw('TRIM(`الاختصاص`) = ?', [$filters['major']]);
        }

        if (!empty($filters['gender'])) {
            $query->whereRaw('TRIM(`الجنس`) = ?', [$filters['gender']]);
        }

        if (!empty($filters['year'])) {
            $query->where('العام الدراسي', $filters['year']);
        }

        if (!empty($filters['search'])) {
            $pattern = '%' . $filters['search'] . '%';
            $normNum = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(`الرقم الامتحاني`), 'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ى', 'ي'))";
            $nameExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT_WS(' ', TRIM(`اسم الطالب`), TRIM(`اسم الاب`), TRIM(`اسم الجد`), TRIM(`اللقب`)), '  ', ' '), 'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ى', 'ي')";
            $normName = "LOWER({$nameExpr})";

            $query->where(function ($q) use ($pattern, $normNum, $normName) {
                $q->whereRaw("{$normNum} LIKE ?", [$pattern])
                    ->orWhereRaw("{$normName} LIKE ?", [$pattern]);
            });
        }

        /* ترتيب تصاعدي حسب الرقم الامتحاني (يدعم الأرقام النصية كأرقام) */
        $query->orderByRaw('CAST(`الرقم الامتحاني` AS UNSIGNED) ASC')->orderBy('الرقم الامتحاني', 'asc');

        $students = $query->paginate(self::PER_PAGE)->withQueryString();

        $academicYears = DB::table('main_table')
            ->select('العام الدراسي')
            ->distinct()
            ->whereNotNull('العام الدراسي')
            ->where('العام الدراسي', '!=', '')
            ->orderByDesc('العام الدراسي')
            ->pluck('العام الدراسي');

        $branches = DB::table('main_table')
            ->selectRaw('TRIM(`الفرع`) AS branch')
            ->distinct()
            ->whereNotNull('الفرع')
            ->whereRaw('TRIM(`الفرع`) != ""')
            ->orderBy('branch')
            ->pluck('branch');

        $majors = DB::table('main_table')
            ->selectRaw('TRIM(`الاختصاص`) AS major')
            ->distinct()
            ->whereNotNull('الاختصاص')
            ->whereRaw('TRIM(`الاختصاص`) != ""')
            ->orderBy('major')
            ->pluck('major');

        $genders = DB::table('main_table')
            ->selectRaw('TRIM(`الجنس`) AS gender')
            ->distinct()
            ->whereNotNull('الجنس')
            ->whereRaw('TRIM(`الجنس`) != ""')
            ->pluck('gender')
            ->sortBy(function ($g) {
                $order = ['ذكر' => 0, 'أنثى' => 1];
                return $order[$g] ?? 2;
            })
            ->values();

        return new StudentListProjection(
            students: $students,
            academicYears: $academicYears,
            branches: $branches,
            majors: $majors,
            genders: $genders,
        );
    }

    public function getGradesById(int $id): ?StudentGradesView
    {
        $gradeColumns = Config::get('grades_catalog.grade_columns', []);
        $select = [
            'id',
            'الرقم الامتحاني',
            'اسم الطالب',
            'اسم الاب',
            'اسم الجد',
            'اللقب',
            'الفرع',
            'الاختصاص',
            'العام الدراسي',
            'النتيجة',
            'المجموع',
            'المعدل',
            'الدور',
        ];
        foreach ($gradeColumns as $col) {
            $select[] = $col;
        }
        $row = DB::table('main_table')->selectRaw(
            implode(', ', array_map(
                fn(string $c) => '`' . str_replace('`', '``', $c) . '`',
                $select
            ))
        )->where('id', $id)->first();
        if (!$row) {
            return null;
        }

        $fullName = trim(implode(' ', [
            $row->{'اسم الطالب'} ?? '',
            $row->{'اسم الاب'} ?? '',
            $row->{'اسم الجد'} ?? '',
            $row->{'اللقب'} ?? '',
        ]));

        $grades = [];
        foreach ($gradeColumns as $columnName) {
            $value = $row->{$columnName} ?? null;
            $grades[] = [
                'subject' => $columnName,
                'score' => $value !== null && $value !== '' ? (string) $value : '',
            ];
        }
        if (empty($grades)) {
            $grades = [
                ['subject' => '', 'score' => ''],
                ['subject' => '', 'score' => ''],
                ['subject' => '', 'score' => ''],
            ];
        }

        return new StudentGradesView(
            id: (int) $row->id,
            fullName: $fullName,
            nameStudent: trim((string) ($row->{'اسم الطالب'} ?? '')),
            nameFather: trim((string) ($row->{'اسم الاب'} ?? '')),
            nameGrandfather: trim((string) ($row->{'اسم الجد'} ?? '')),
            nameSurname: trim((string) ($row->{'اللقب'} ?? '')),
            examNumber: (string) ($row->{'الرقم الامتحاني'} ?? ''),
            branch: isset($row->{'الفرع'}) ? trim((string) $row->{'الفرع'}) : '',
            major: isset($row->{'الاختصاص'}) ? trim((string) $row->{'الاختصاص'}) : '',
            academicYear: (string) ($row->{'العام الدراسي'} ?? ''),
            result: (string) ($row->{'النتيجة'} ?? ''),
            grades: $grades,
            total: (string) ($row->{'المجموع'} ?? $row->{'مجموع'} ?? ''),
            average: (string) ($row->{'المعدل'} ?? $row->{'معدل'} ?? ''),
            round: (string) ($row->{'الدور'} ?? $row->{'دور'} ?? ''),
        );
    }

    public function getStudentById(int $id): ?Student
    {
        $row = DB::table('main_table')
            ->selectRaw("
                id,
                `الرقم الامتحاني` AS exam_number,
                CONCAT_WS(' ', `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`) AS full_name,
                `العام الدراسي` AS academic_year,
                `النتيجة` AS result,
                TRIM(`الفرع`) AS branch,
                TRIM(`الاختصاص`) AS major,
                TRIM(`الجنس`) AS gender
            ")
            ->where('id', $id)
            ->first();

        if (!$row) {
            return null;
        }

        return Student::fromObject($row);
    }
}

