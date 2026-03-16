<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\Student;
use App\Domain\Student\StudentDocumentInfo;
use App\Domain\Student\StudentGradesView;
use App\Domain\Student\StudentListProjection;
use App\Domain\Student\StudentQueryRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تنفيذ قراءة الطلاب على MySQL (CQRS — Query side).
 * يدعم الجداول المُطبّعة (students, student_personal, student_academic, student_grades) عند وجودها، وإلا main_table.
 */
final class MySQLStudentQueryRepository implements StudentQueryRepository
{
    private const PER_PAGE = 20;

    private const CACHE_KEY_FILTER_LISTS = 'student_filters.lists';

    private const CACHE_TTL_SECONDS = 600;

    private function useNormalizedSchema(): bool
    {
        return Schema::hasTable('students');
    }

    private function studentGradesUsesMajorSubject(): bool
    {
        return Schema::hasColumn('student_grades', 'major_subject_id');
    }

    /** المجموع يعرض كعدد صحيح فقط (بدون كسور). */
    private function formatTotal(mixed $value): string
    {
        $v = $value === null ? '' : trim((string) $value);
        if ($v === '' || ! is_numeric($v)) {
            return $v;
        }
        return (string) (int) round((float) $v);
    }

    /** النتيجة في فورم الدرجات: تُعرض فقط إن كانت من الخيارات المسموحة، وإلا فارغة. */
    private function sanitizeResult(mixed $value): string
    {
        $v = $value === null ? '' : trim((string) $value);
        $allowed = Config::get('grades_catalog.result_options', []);
        return $v !== '' && in_array($v, $allowed, true) ? $v : '';
    }

    public function listWithFilters(array $filters): StudentListProjection
    {
        if ($this->useNormalizedSchema()) {
            return $this->listWithFiltersNormalized($filters);
        }

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

        $this->applyListFilters($query, $filters);

        /* ترتيب تصاعدي حسب الرقم الامتحاني (يدعم الأرقام النصية كأرقام) */
        $query->orderByRaw('CAST(`الرقم الامتحاني` AS UNSIGNED) ASC')->orderBy('الرقم الامتحاني', 'asc');

        $students = $query->paginate(self::PER_PAGE)->withQueryString();

        $filterLists = $this->getFilterListsFromCache();

        return new StudentListProjection(
            students: $students,
            academicYears: $filterLists['academicYears'],
            branches: $filterLists['branches'],
            majors: $filterLists['majors'],
            genders: $filterLists['genders'],
            resultOptions: $filterLists['resultOptions'],
            roundOptions: $filterLists['roundOptions'],
        );
    }

    private function listWithFiltersNormalized(array $filters): StudentListProjection
    {
        $query = DB::table('students as s')
            ->join('student_personal as p', 'p.student_id', '=', 's.id')
            ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
            ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
            ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
            ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
            ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
            ->selectRaw("
                s.id,
                s.exam_number,
                CONCAT_WS(' ', p.first_name, p.father_name, p.grandfather_name, p.surname) AS full_name,
                y.year_label AS academic_year,
                rt.name_ar AS result,
                b.name_ar AS branch,
                m.name_ar AS major,
                p.gender
            ");

        $this->applyListFiltersNormalized($query, $filters);
        $query->orderByRaw('CAST(s.exam_number AS UNSIGNED) ASC')->orderBy('s.exam_number', 'asc');

        $students = $query->paginate(self::PER_PAGE)->withQueryString();
        $filterLists = $this->getFilterListsFromCache();

        return new StudentListProjection(
            students: $students,
            academicYears: $filterLists['academicYears'],
            branches: $filterLists['branches'],
            majors: $filterLists['majors'],
            genders: $filterLists['genders'],
            resultOptions: $filterLists['resultOptions'],
            roundOptions: $filterLists['roundOptions'],
        );
    }

    /** @param \Illuminate\Database\Query\Builder $query */
    private function applyListFiltersNormalized($query, array $filters): void
    {
        if (! empty($filters['branch'])) {
            $query->where('b.name_ar', $filters['branch']);
        }
        if (! empty($filters['major'])) {
            $query->where('m.name_ar', $filters['major']);
        }
        if (! empty($filters['gender'])) {
            $query->where('p.gender', $filters['gender']);
        }
        if (! empty($filters['year'])) {
            $query->where('y.year_label', $filters['year']);
        }
        if (! empty($filters['search'])) {
            $pattern = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($pattern): void {
                $q->where('s.exam_number', 'like', $pattern)
                    ->orWhereRaw("CONCAT_WS(' ', p.first_name, p.father_name, p.grandfather_name, p.surname) LIKE ?", [$pattern]);
            });
        }
    }

    /**
     * قوائم الفلترة (أعوام، فروع، اختصاصات، جنس) مع كاش.
     * من الجداول المُطبّعة إن وُجدت، وإلا من main_table.
     *
     * @return array{academicYears: \Illuminate\Support\Collection, branches: \Illuminate\Support\Collection, majors: \Illuminate\Support\Collection, genders: \Illuminate\Support\Collection, resultOptions: \Illuminate\Support\Collection, roundOptions: \Illuminate\Support\Collection}
     */
    private function getFilterListsFromCache(): array
    {
        return Cache::remember(self::CACHE_KEY_FILTER_LISTS, self::CACHE_TTL_SECONDS, function (): array {
            if ($this->useNormalizedSchema()) {
                $academicYears = DB::table('academic_years')->orderByDesc('year_label')->pluck('year_label');
                $branches = DB::table('branches')->orderBy('name_ar')->pluck('name_ar');
                $majors = DB::table('majors')->orderBy('name_ar')->pluck('name_ar');
                $genders = DB::table('student_personal')
                    ->select('gender')
                    ->distinct()
                    ->whereNotNull('gender')
                    ->where('gender', '!=', '')
                    ->pluck('gender')
                    ->sortBy(fn ($g) => ['ذكر' => 0, 'أنثى' => 1][$g] ?? 2)
                    ->values();
            } else {
                $academicYears = DB::table('main_table')
                    ->select('العام الدراسي')->distinct()->whereNotNull('العام الدراسي')->where('العام الدراسي', '!=', '')
                    ->orderByDesc('العام الدراسي')->pluck('العام الدراسي');
                $branches = DB::table('main_table')
                    ->selectRaw('TRIM(`الفرع`) AS branch')->distinct()->whereNotNull('الفرع')->whereRaw('TRIM(`الفرع`) != ""')
                    ->orderBy('branch')->pluck('branch');
                $majors = DB::table('main_table')
                    ->selectRaw('TRIM(`الاختصاص`) AS major')->distinct()->whereNotNull('الاختصاص')->whereRaw('TRIM(`الاختصاص`) != ""')
                    ->orderBy('major')->pluck('major');
                $genders = DB::table('main_table')
                    ->selectRaw('TRIM(`الجنس`) AS gender')->distinct()->whereNotNull('الجنس')->whereRaw('TRIM(`الجنس`) != ""')
                    ->pluck('gender')->sortBy(fn ($g) => ['ذكر' => 0, 'أنثى' => 1][$g] ?? 2)->values();
            }

            $allowedResults = Config::get('grades_catalog.result_options', ['ناجح', 'ناجحة', 'راسب', 'راسبة', 'معيد', 'معيده']);
            $resultOptions = Schema::hasTable('result_types')
                ? DB::table('result_types')->whereIn('name_ar', $allowedResults)->orderBy('sort_order')->pluck('name_ar')
                : collect($allowedResults);
            $allowedRounds = Config::get('grades_catalog.round_options', ['الاول', 'الثاني', 'الثالث', 'الاول تكميلي', 'الثاني تكميلي', 'الثالث تكميلي']);
            $roundOptions = Schema::hasTable('round_options')
                ? DB::table('round_options')->whereIn('name_ar', $allowedRounds)->orderBy('sort_order')->pluck('name_ar')
                : collect($allowedRounds);

            return [
                'academicYears' => $academicYears,
                'branches' => $branches,
                'majors' => $majors,
                'genders' => $genders,
                'resultOptions' => $resultOptions,
                'roundOptions' => $roundOptions,
            ];
        });
    }

    public function listIdsWithFilters(array $filters): array
    {
        if ($this->useNormalizedSchema()) {
            $query = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->select('s.id');
            $this->applyListFiltersNormalized($query, $filters);
            $query->orderByRaw('CAST(s.exam_number AS UNSIGNED) ASC')->orderBy('s.exam_number', 'asc');
            return $query->pluck('id')->map(static fn ($id) => (int) $id)->values()->all();
        }
        $query = DB::table('main_table')->select('id');
        $this->applyListFilters($query, $filters);
        $query->orderByRaw('CAST(`الرقم الامتحاني` AS UNSIGNED) ASC')->orderBy('الرقم الامتحاني', 'asc');
        return $query->pluck('id')->map(static fn ($id) => (int) $id)->values()->all();
    }

    public function listFailedIdsWithFilters(array $filters): array
    {
        if ($this->useNormalizedSchema()) {
            $query = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
                ->select('s.id')
                ->whereIn('rt.name_ar', ['راسب', 'راسبة', 'معيد', 'معيده']);
            $this->applyListFiltersNormalized($query, $filters);
            $query->orderByRaw('CAST(s.exam_number AS UNSIGNED) ASC')->orderBy('s.exam_number', 'asc');
            return $query->pluck('id')->map(static fn ($id) => (int) $id)->values()->all();
        }
        $query = DB::table('main_table')->select('id');
        $this->applyListFilters($query, $filters);
        $query->whereIn('النتيجة', ['راسب', 'راسبة', 'معيد', 'معيده']);
        $query->orderByRaw('CAST(`الرقم الامتحاني` AS UNSIGNED) ASC')->orderBy('الرقم الامتحاني', 'asc');
        return $query->pluck('id')->map(static fn ($id) => (int) $id)->values()->all();
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array{branch?: string, major?: string, gender?: string, year?: string, search?: string}  $filters
     */
    private function applyListFilters($query, array $filters): void
    {
        if (! empty($filters['branch'])) {
            $query->whereRaw('TRIM(`الفرع`) = ?', [$filters['branch']]);
        }

        if (! empty($filters['major'])) {
            $query->whereRaw('TRIM(`الاختصاص`) = ?', [$filters['major']]);
        }

        if (! empty($filters['gender'])) {
            $query->whereRaw('TRIM(`الجنس`) = ?', [$filters['gender']]);
        }

        if (! empty($filters['year'])) {
            $query->where('العام الدراسي', $filters['year']);
        }

        if (! empty($filters['search'])) {
            $pattern = '%'.$filters['search'].'%';
            $normNum = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(`الرقم الامتحاني`), 'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ى', 'ي'))";
            $nameExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT_WS(' ', TRIM(`اسم الطالب`), TRIM(`اسم الاب`), TRIM(`اسم الجد`), TRIM(`اللقب`)), '  ', ' '), 'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ى', 'ي')";
            $normName = "LOWER({$nameExpr})";

            $query->where(function ($q) use ($pattern, $normNum, $normName) {
                $q->whereRaw("{$normNum} LIKE ?", [$pattern])
                    ->orWhereRaw("{$normName} LIKE ?", [$pattern]);
            });
        }
    }

    public function getGradesById(int $id): ?StudentGradesView
    {
        if ($this->useNormalizedSchema()) {
            return $this->getGradesByIdNormalized($id);
        }

        $gradeColumns = Config::get('grades_catalog.grade_columns', []);
        $select = ['id', 'الرقم الامتحاني', 'اسم الطالب', 'اسم الاب', 'اسم الجد', 'اللقب', 'الجنس', 'الفرع', 'الاختصاص', 'العام الدراسي', 'النتيجة', 'المجموع', 'المعدل', 'الدور'];
        foreach ($gradeColumns as $col) {
            $select[] = $col;
        }
        $row = DB::table('main_table')->selectRaw(implode(', ', array_map(fn (string $c) => '`'.str_replace('`', '``', $c).'`', $select)))->where('id', $id)->first();
        if (! $row) {
            return null;
        }
        $fullName = trim(implode(' ', [$row->{'اسم الطالب'} ?? '', $row->{'اسم الاب'} ?? '', $row->{'اسم الجد'} ?? '', $row->{'اللقب'} ?? '']));
        $grades = [];
        foreach ($gradeColumns as $columnName) {
            $value = $row->{$columnName} ?? null;
            $grades[] = ['subject' => $columnName, 'score' => $value !== null && $value !== '' && is_numeric($value) ? (string) (int) round((float) $value) : ($value !== null && $value !== '' ? (string) $value : '')];
        }
        if (empty($grades)) {
            $grades = [['subject' => '', 'score' => ''], ['subject' => '', 'score' => ''], ['subject' => '', 'score' => '']];
        }
        return new StudentGradesView(
            id: (int) $row->id,
            fullName: $fullName,
            nameStudent: trim((string) ($row->{'اسم الطالب'} ?? '')),
            nameFather: trim((string) ($row->{'اسم الاب'} ?? '')),
            nameGrandfather: trim((string) ($row->{'اسم الجد'} ?? '')),
            nameSurname: trim((string) ($row->{'اللقب'} ?? '')),
            examNumber: (string) ($row->{'الرقم الامتحاني'} ?? ''),
            gender: trim((string) ($row->{'الجنس'} ?? '')),
            branch: isset($row->{'الفرع'}) ? trim((string) $row->{'الفرع'}) : '',
            major: isset($row->{'الاختصاص'}) ? trim((string) $row->{'الاختصاص'}) : '',
            academicYear: (string) ($row->{'العام الدراسي'} ?? ''),
            result: $this->sanitizeResult($row->{'النتيجة'} ?? ''),
            grades: $grades,
            total: (string) ($row->{'المجموع'} ?? $row->{'مجموع'} ?? ''),
            average: (string) ($row->{'المعدل'} ?? $row->{'معدل'} ?? ''),
            round: (string) ($row->{'الدور'} ?? $row->{'دور'} ?? ''),
        );
    }

    private function getGradesByIdNormalized(int $id): ?StudentGradesView
    {
        $row = DB::table('students as s')
            ->join('student_personal as p', 'p.student_id', '=', 's.id')
            ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
            ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
            ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
            ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
            ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
            ->where('s.id', $id)
            ->selectRaw("s.id, s.exam_number, p.first_name, p.father_name, p.grandfather_name, p.surname, p.gender, b.name_ar AS branch, m.name_ar AS major, m.id AS major_id, y.year_label AS academic_year, y.id AS academic_year_id, rt.name_ar AS result, a.total, a.average, a.round")
            ->first();
        if (! $row) {
            return null;
        }
        $fullName = trim(implode(' ', [$row->first_name ?? '', $row->father_name ?? '', $row->grandfather_name ?? '', $row->surname ?? '']));
        $majorId = $row->major_id ?? null;
        $academicYearId = $row->academic_year_id ?? null;

        if ($this->studentGradesUsesMajorSubject() && $majorId !== null && $academicYearId !== null) {
            $gradeColumns = DB::table('major_subjects as ms')
                ->join('subjects as sub', 'sub.id', '=', 'ms.subject_id')
                ->where('ms.major_id', $majorId)
                ->orderBy('ms.sort_order')
                ->pluck('sub.name_ar');
            $scoreMap = DB::table('student_grades as g')
                ->join('major_subjects as ms', 'ms.id', '=', 'g.major_subject_id')
                ->join('subjects as sub', 'sub.id', '=', 'ms.subject_id')
                ->where('g.student_id', $id)
                ->where('g.academic_year_id', $academicYearId)
                ->pluck('g.score', 'sub.name_ar')
                ->all();
        } else {
            $gradeColumns = Config::get('grades_catalog.grade_columns', []);
            $scoreMap = DB::table('student_grades as g')
                ->join('subjects as sub', 'sub.id', '=', 'g.subject_id')
                ->where('g.student_id', $id)
                ->pluck('g.score', 'sub.name_ar')
                ->all();
        }

        $grades = [];
        foreach ($gradeColumns as $name) {
            $raw = $scoreMap[$name] ?? null;
            $scoreStr = $raw !== null && $raw !== '' && is_numeric($raw)
                ? (string) (int) round((float) $raw)
                : (isset($scoreMap[$name]) ? (string) $scoreMap[$name] : '');
            $grades[] = ['subject' => $name, 'score' => $scoreStr];
        }
        if (empty($grades)) {
            $grades = [['subject' => '', 'score' => ''], ['subject' => '', 'score' => ''], ['subject' => '', 'score' => '']];
        }
        return new StudentGradesView(
            id: (int) $row->id,
            fullName: $fullName,
            nameStudent: trim((string) ($row->first_name ?? '')),
            nameFather: trim((string) ($row->father_name ?? '')),
            nameGrandfather: trim((string) ($row->grandfather_name ?? '')),
            nameSurname: trim((string) ($row->surname ?? '')),
            examNumber: (string) ($row->exam_number ?? ''),
            gender: trim((string) ($row->gender ?? '')),
            branch: trim((string) ($row->branch ?? '')),
            major: trim((string) ($row->major ?? '')),
            academicYear: (string) ($row->academic_year ?? ''),
            result: $this->sanitizeResult($row->result ?? ''),
            grades: $grades,
            total: $this->formatTotal($row->total ?? ''),
            average: (string) ($row->average ?? ''),
            round: (string) ($row->round ?? ''),
        );
    }

    public function getStudentById(int $id): ?Student
    {
        if ($this->useNormalizedSchema()) {
            $row = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
                ->where('s.id', $id)
                ->selectRaw("s.id, s.exam_number, CONCAT_WS(' ', p.first_name, p.father_name, p.grandfather_name, p.surname) AS full_name, y.year_label AS academic_year, rt.name_ar AS result, b.name_ar AS branch, m.name_ar AS major, p.gender")
                ->first();
        } else {
            $row = DB::table('main_table')
                ->selectRaw("id, `الرقم الامتحاني` AS exam_number, CONCAT_WS(' ', `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`) AS full_name, `العام الدراسي` AS academic_year, `النتيجة` AS result, TRIM(`الفرع`) AS branch, TRIM(`الاختصاص`) AS major, TRIM(`الجنس`) AS gender")
                ->where('id', $id)->first();
        }
        if (! $row) {
            return null;
        }
        return Student::fromObject($row);
    }

    public function getStudentDocumentInfo(int $id): ?StudentDocumentInfo
    {
        if ($this->useNormalizedSchema()) {
            $row = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
                ->where('s.id', $id)
                ->selectRaw("s.exam_number, p.first_name, p.father_name, p.grandfather_name, p.surname, p.gender, p.birth_date, p.birth_place, p.mother_full_name, b.name_ar AS branch, m.name_ar AS specialization, y.year_label AS academic_year, rt.name_ar AS result, a.round, a.last_school, a.middle_doc_number, a.middle_doc_date, a.issuing_authority")
                ->first();
            if (! $row) {
                return null;
            }
            $trim = static fn ($v) => isset($v) ? trim((string) $v) : '';
            $fullName = trim(implode(' ', array_filter([$trim($row->first_name), $trim($row->father_name), $trim($row->grandfather_name), $trim($row->surname)])));
            return new StudentDocumentInfo(
                fullName: $fullName,
                examNumber: $trim($row->exam_number),
                birthDate: $row->birth_date ? (is_string($row->birth_date) ? $row->birth_date : $row->birth_date->format('Y-m-d')) : '',
                birthPlace: $trim($row->birth_place ?? ''),
                motherName: $trim($row->mother_full_name ?? ''),
                branch: $trim($row->branch ?? ''),
                specialization: $trim($row->specialization ?? ''),
                lastSchool: $trim($row->last_school ?? ''),
                middleDocNumber: $trim($row->middle_doc_number ?? ''),
                middleDocDate: $row->middle_doc_date ? (is_string($row->middle_doc_date) ? $row->middle_doc_date : $row->middle_doc_date->format('Y-m-d')) : '',
                issuingAuthority: $trim($row->issuing_authority ?? ''),
                academicYear: $trim($row->academic_year ?? ''),
                result: $trim($row->result ?? ''),
                round: $trim($row->round ?? ''),
                gender: $trim($row->gender ?? ''),
            );
        }
        $row = DB::table('main_table')->where('id', $id)->first();
        if (! $row) {
            return null;
        }
        $trim = static fn ($v) => isset($v) ? trim((string) $v) : '';
        $fullName = trim(implode(' ', array_filter([$trim($row->{'اسم الطالب'}), $trim($row->{'اسم الاب'}), $trim($row->{'اسم الجد'}), $trim($row->{'اللقب'})])));
        return new StudentDocumentInfo(
            fullName: $fullName,
            examNumber: $trim($row->{'الرقم الامتحاني'}),
            birthDate: $trim($row->{'التولد'} ?? ''),
            birthPlace: $trim($row->{'محل الولادة'} ?? ''),
            motherName: $trim($row->{'اسم الام الكامل'} ?? ''),
            branch: $trim($row->{'الفرع'} ?? ''),
            specialization: $trim($row->{'الاختصاص'} ?? ''),
            lastSchool: $trim($row->{'اخر مدرسة كان فيها الطالب'} ?? ''),
            middleDocNumber: $trim($row->{'رقم الوثيقة المتوسطة'} ?? ''),
            middleDocDate: $trim($row->{'تاريخها'} ?? ''),
            issuingAuthority: $trim($row->{'جهة الاصدار'} ?? ''),
            academicYear: $trim($row->{'العام الدراسي'} ?? ''),
            result: $trim($row->{'النتيجة'} ?? ''),
            round: $trim($row->{'الدور'} ?? ''),
            gender: $trim($row->{'الجنس'} ?? ''),
        );
    }

    public function getAcademicYearsForForm(): array
    {
        $currentYear = (int) date('Y');
        $currentAcademicYear = ($currentYear - 1).'-'.$currentYear;

        $filterLists = $this->getFilterListsFromCache();
        $years = $filterLists['academicYears']->map(fn ($y) => (string) $y)->values()->all();

        if (! in_array($currentAcademicYear, $years, true)) {
            array_unshift($years, $currentAcademicYear);
        }

        return $years;
    }
}
