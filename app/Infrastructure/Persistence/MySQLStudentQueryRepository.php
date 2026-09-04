<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\Student;
use App\Domain\Student\StudentDocumentInfo;
use App\Domain\Student\StudentGradesView;
use App\Domain\Student\StudentListProjection;
use App\Domain\Student\StudentQueryRepository;
use App\Support\AcademicYearOptions;
use App\Support\GenderFilterVariants;
use App\Support\ResultFilterVariants;
use Illuminate\Support\Carbon;
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

    private function formatBirthDateForInput(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        try {
            $formatted = Carbon::parse($value)->format('Y-m-d');
            if ($formatted === '1000-01-01') {
                return '';
            }

            return $formatted;
        } catch (\Throwable) {
            return trim((string) $value);
        }
    }

    private function useNormalizedSchema(): bool
    {
        return Schema::hasTable('students') || ! Schema::hasTable('main_table');
    }

    private function studentNotesCountExpr(string $studentIdColumn): string
    {
        if (! Schema::hasTable('student_notes')) {
            return '0';
        }

        return "(SELECT COUNT(*) FROM student_notes n WHERE n.student_id = {$studentIdColumn})";
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

    /**
     * المجموع بدالة الجمع من درجات المواد.
     *
     * @param  list<array{subject?: string, score?: string}>  $grades
     */
    private function sumGradeScores(array $grades): int
    {
        $total = 0;
        foreach ($grades as $row) {
            if (! is_array($row)) {
                continue;
            }
            $score = trim((string) ($row['score'] ?? ''));
            if ($score !== '' && is_numeric($score)) {
                $total += (int) round((float) $score);
            }
        }

        return $total;
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

        $withoutExpr = '0';
        $withExpr = '0';
        if (Schema::hasTable('certificate')) {
            if (Schema::hasColumn('certificate', 'student_id')) {
                $withoutExpr = "(SELECT COUNT(*) FROM certificate c WHERE c.student_id = id AND c.type = 'without_grades')";
                $withExpr = "(SELECT COUNT(*) FROM certificate c WHERE c.student_id = id AND c.type = 'with_grades')";
            } else {
                $withoutExpr = "(SELECT COUNT(*) FROM certificate c WHERE c.exam_number = `الرقم الامتحاني` AND c.type = 'without_grades')";
                $withExpr = "(SELECT COUNT(*) FROM certificate c WHERE c.exam_number = `الرقم الامتحاني` AND c.type = 'with_grades')";
            }
        }
        $docsExpr = '0';
        if (Schema::hasTable('records')) {
            if (Schema::hasColumn('records', 'student_id')) {
                $docsExpr = '(SELECT COUNT(*) FROM records r WHERE r.student_id = id)';
            } else {
                // في البنية القديمة بدون student_id لا نحسب الوثائق بدقة هنا لتفادي التباس الأعمدة.
                $docsExpr = '0';
            }
        }
        $notesExpr = $this->studentNotesCountExpr('id');
        $enrollmentExpr = Schema::hasColumn('main_table', 'رقم القيد')
            ? "TRIM(COALESCE(`رقم القيد`, ''))"
            : "''";

        $query = DB::table('main_table')
            ->selectRaw("
                id,
                `الرقم الامتحاني` AS exam_number,
                CONCAT_WS(' ', `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`) AS full_name,
                `العام الدراسي` AS academic_year,
                `النتيجة` AS result,
                TRIM(`الفرع`) AS branch,
                TRIM(`الاختصاص`) AS major,
                TRIM(`الجنس`) AS gender,
                {$withoutExpr} AS attest_without_count,
                {$withExpr} AS attest_with_count,
                {$docsExpr} AS docs_count,
                (({$withoutExpr}) + ({$withExpr}) + ({$docsExpr}) + ({$notesExpr})) AS profile_total_count,
                {$enrollmentExpr} AS enrollment_number
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
        $withoutExpr = '0';
        $withExpr = '0';
        if (Schema::hasTable('certificate')) {
            if (Schema::hasColumn('certificate', 'student_id')) {
                $withoutExpr = "(SELECT COUNT(*) FROM certificate c WHERE c.student_id = s.id AND c.type = 'without_grades')";
                $withExpr = "(SELECT COUNT(*) FROM certificate c WHERE c.student_id = s.id AND c.type = 'with_grades')";
            } else {
                $withoutExpr = "(SELECT COUNT(*) FROM certificate c WHERE c.exam_number = s.exam_number AND c.type = 'without_grades')";
                $withExpr = "(SELECT COUNT(*) FROM certificate c WHERE c.exam_number = s.exam_number AND c.type = 'with_grades')";
            }
        }
        $docsExpr = '0';
        if (Schema::hasTable('records')) {
            if (Schema::hasColumn('records', 'student_id')) {
                $docsExpr = '(SELECT COUNT(*) FROM records r WHERE r.student_id = s.id)';
            } else {
                $docsExpr = '(SELECT COUNT(*) FROM records r WHERE r.`الرقم الامتحاني` = s.exam_number)';
            }
        }
        $notesExpr = $this->studentNotesCountExpr('s.id');

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
                p.gender,
                {$withoutExpr} AS attest_without_count,
                {$withExpr} AS attest_with_count,
                {$docsExpr} AS docs_count,
                (({$withoutExpr}) + ({$withExpr}) + ({$docsExpr}) + ({$notesExpr})) AS profile_total_count,
                TRIM(COALESCE(a.enrollment_number, '')) AS enrollment_number
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
            $genderValues = GenderFilterVariants::expand((string) $filters['gender']);
            if (count($genderValues) === 1) {
                $query->where('p.gender', $genderValues[0]);
            } else {
                $query->whereIn('p.gender', $genderValues);
            }
        }
        if (! empty($filters['year'])) {
            $query->where('y.year_label', $filters['year']);
        }
        if (! empty($filters['round'])) {
            $query->where('a.round', $filters['round']);
        }
        if (! empty($filters['result'])) {
            $resultValues = ResultFilterVariants::expand((string) $filters['result']);
            if (count($resultValues) === 1) {
                $query->where('rt.name_ar', $resultValues[0]);
            } else {
                $query->whereIn('rt.name_ar', $resultValues);
            }
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
        /** @var array{academicYears: \Illuminate\Support\Collection, branches: \Illuminate\Support\Collection, majors: \Illuminate\Support\Collection, genders: \Illuminate\Support\Collection, resultOptions: \Illuminate\Support\Collection, roundOptions: \Illuminate\Support\Collection} $lists */
        $lists = Cache::remember(self::CACHE_KEY_FILTER_LISTS, self::CACHE_TTL_SECONDS, function (): array {
            if ($this->useNormalizedSchema()) {
                $academicYears = DB::table('academic_years')->orderByDesc('year_label')->pluck('year_label');
                $branches = DB::table('branches')->orderBy('name_ar')->pluck('name_ar');
                $majors = DB::table('majors')->orderBy('name_ar')->pluck('name_ar');
                $genders = DB::table('student_personal')
                    ->select('gender')
                    ->distinct()
                    ->whereNotNull('gender')
                    ->where('gender', '!=', '')
                    ->pluck('gender');
                $genders = GenderFilterVariants::normalizeOptions($genders);
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
                    ->pluck('gender');
                $genders = GenderFilterVariants::normalizeOptions($genders);
            }

            $allowedResults = Config::get('grades_catalog.result_options', ['ناجح', 'ناجحة', 'ناجحه', 'راسب', 'راسبة', 'معيد', 'معيده', 'معيدة', 'حجب']);
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

        $lists['genders'] = GenderFilterVariants::normalizeOptions($lists['genders'] ?? []);

        return $lists;
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
                ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
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

    public function countWithFilters(array $filters): int
    {
        if ($this->useNormalizedSchema()) {
            $query = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id');
            $this->applyListFiltersNormalized($query, $filters);

            return (int) $query->count('s.id');
        }

        $query = DB::table('main_table');
        $this->applyListFilters($query, $filters);

        return (int) $query->count('id');
    }

    public function countGendersWithFilters(array $filters): array
    {
        $male = 0;
        $female = 0;

        if ($this->useNormalizedSchema()) {
            $query = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
                ->selectRaw('TRIM(p.gender) AS gender_label, COUNT(*) AS total');
            $this->applyListFiltersNormalized($query, $filters);
            $rows = $query->groupByRaw('TRIM(p.gender)')->get();
        } else {
            $query = DB::table('main_table')
                ->selectRaw('TRIM(`الجنس`) AS gender_label, COUNT(*) AS total');
            $this->applyListFilters($query, $filters);
            $rows = $query->groupByRaw('TRIM(`الجنس`)')->get();
        }

        foreach ($rows as $row) {
            $label = trim((string) ($row->gender_label ?? ''));
            $total = (int) ($row->total ?? 0);
            if ($label === 'ذكر') {
                $male += $total;
            } elseif (in_array($label, GenderFilterVariants::FEMALE_VARIANTS, true)) {
                $female += $total;
            }
        }

        return ['male' => $male, 'female' => $female];
    }

    public function getFilterLists(): array
    {
        return $this->getFilterListsFromCache();
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
                ->whereIn('rt.name_ar', ['راسب', 'راسبة', 'معيد', 'معيده', 'معيدة']);
            $this->applyListFiltersNormalized($query, $filters);
            $query->orderByRaw('CAST(s.exam_number AS UNSIGNED) ASC')->orderBy('s.exam_number', 'asc');

            return $query->pluck('id')->map(static fn ($id) => (int) $id)->values()->all();
        }
        $query = DB::table('main_table')->select('id');
        $this->applyListFilters($query, $filters);
        $query->whereIn('النتيجة', ['راسب', 'راسبة', 'معيد', 'معيده', 'معيدة']);
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
            $genderValues = GenderFilterVariants::expand((string) $filters['gender']);
            if (count($genderValues) === 1) {
                $query->whereRaw('TRIM(`الجنس`) = ?', [$genderValues[0]]);
            } else {
                $placeholders = implode(', ', array_fill(0, count($genderValues), '?'));
                $query->whereRaw('TRIM(`الجنس`) IN ('.$placeholders.')', $genderValues);
            }
        }

        if (! empty($filters['year'])) {
            $query->where('العام الدراسي', $filters['year']);
        }

        if (! empty($filters['round'])) {
            $query->whereRaw('TRIM(`الدور`) = ?', [$filters['round']]);
        }

        if (! empty($filters['result'])) {
            $resultValues = ResultFilterVariants::expand((string) $filters['result']);
            if (count($resultValues) === 1) {
                $query->whereRaw('TRIM(`النتيجة`) = ?', [$resultValues[0]]);
            } else {
                $placeholders = implode(', ', array_fill(0, count($resultValues), '?'));
                $query->whereRaw('TRIM(`النتيجة`) IN ('.$placeholders.')', $resultValues);
            }
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
        $select = ['id', 'الرقم الامتحاني', 'اسم الطالب', 'اسم الاب', 'اسم الجد', 'اللقب', 'الجنس', 'التولد', 'محل الولادة', 'اسم الام الكامل', 'الفرع', 'الاختصاص', 'العام الدراسي', 'اخر مدرسة كان فيها الطالب', 'رقم الوثيقة المتوسطة', 'تاريخها', 'جهة الاصدار', 'النتيجة', 'المجموع', 'المعدل', 'الدور'];
        if (Schema::hasColumn('main_table', 'رقم القيد')) {
            $select[] = 'رقم القيد';
        }
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
            birthDate: $this->formatBirthDateForInput($row->{'التولد'} ?? null),
            birthPlace: trim((string) ($row->{'محل الولادة'} ?? '')),
            motherFullName: trim((string) ($row->{'اسم الام الكامل'} ?? '')),
            gender: trim((string) ($row->{'الجنس'} ?? '')),
            branch: isset($row->{'الفرع'}) ? trim((string) $row->{'الفرع'}) : '',
            major: isset($row->{'الاختصاص'}) ? trim((string) $row->{'الاختصاص'}) : '',
            academicYear: (string) ($row->{'العام الدراسي'} ?? ''),
            lastSchool: trim((string) ($row->{'اخر مدرسة كان فيها الطالب'} ?? '')),
            middleDocNumber: trim((string) ($row->{'رقم الوثيقة المتوسطة'} ?? '')),
            middleDocDate: $this->formatBirthDateForInput($row->{'تاريخها'} ?? null),
            issuingAuthority: trim((string) ($row->{'جهة الاصدار'} ?? '')),
            result: $this->sanitizeResult($row->{'النتيجة'} ?? ''),
            grades: $grades,
            total: (string) $this->sumGradeScores($grades),
            average: (string) ($row->{'المعدل'} ?? $row->{'معدل'} ?? ''),
            round: (string) ($row->{'الدور'} ?? $row->{'دور'} ?? ''),
            enrollmentNumber: isset($row->{'رقم القيد'}) ? trim((string) $row->{'رقم القيد'}) : '',
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
            ->selectRaw('s.id, s.exam_number, p.first_name, p.father_name, p.grandfather_name, p.surname, p.gender, p.birth_date, p.birth_place, p.mother_full_name, b.name_ar AS branch, m.name_ar AS major, m.id AS major_id, y.year_label AS academic_year, y.id AS academic_year_id, rt.name_ar AS result, a.total, a.average, a.round, a.last_school, a.middle_doc_number, a.middle_doc_date, a.issuing_authority, a.enrollment_number')
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
            birthDate: $this->formatBirthDateForInput($row->birth_date ?? null),
            birthPlace: trim((string) ($row->birth_place ?? '')),
            motherFullName: trim((string) ($row->mother_full_name ?? '')),
            gender: trim((string) ($row->gender ?? '')),
            branch: trim((string) ($row->branch ?? '')),
            major: trim((string) ($row->major ?? '')),
            academicYear: (string) ($row->academic_year ?? ''),
            lastSchool: trim((string) ($row->last_school ?? '')),
            middleDocNumber: trim((string) ($row->middle_doc_number ?? '')),
            middleDocDate: $this->formatBirthDateForInput($row->middle_doc_date ?? null),
            issuingAuthority: trim((string) ($row->issuing_authority ?? '')),
            result: $this->sanitizeResult($row->result ?? ''),
            grades: $grades,
            total: (string) $this->sumGradeScores($grades),
            average: (string) ($row->average ?? ''),
            round: (string) ($row->round ?? ''),
            enrollmentNumber: trim((string) ($row->enrollment_number ?? '')),
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
                ->selectRaw("s.id, s.exam_number, CONCAT_WS(' ', p.first_name, p.father_name, p.grandfather_name, p.surname) AS full_name, y.year_label AS academic_year, rt.name_ar AS result, b.name_ar AS branch, m.name_ar AS major, p.gender, a.round")
                ->first();
        } else {
            $row = DB::table('main_table')
                ->selectRaw("id, `الرقم الامتحاني` AS exam_number, CONCAT_WS(' ', `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`) AS full_name, `العام الدراسي` AS academic_year, `النتيجة` AS result, TRIM(`الفرع`) AS branch, TRIM(`الاختصاص`) AS major, TRIM(`الجنس`) AS gender, TRIM(`الدور`) AS round")
                ->where('id', $id)->first();
        }
        if (! $row) {
            return null;
        }

        return Student::fromObject($row);
    }

    public function findNextStudentIdByExamNumber(string $examNumber): ?int
    {
        $examNumber = trim($examNumber);
        if ($examNumber === '') {
            return null;
        }

        if ($this->useNormalizedSchema()) {
            $row = DB::table('students as s')
                ->where(function ($query) use ($examNumber): void {
                    $query->whereRaw('CAST(s.exam_number AS UNSIGNED) > CAST(? AS UNSIGNED)', [$examNumber])
                        ->orWhere(function ($inner) use ($examNumber): void {
                            $inner->whereRaw('CAST(s.exam_number AS UNSIGNED) = CAST(? AS UNSIGNED)', [$examNumber])
                                ->where('s.exam_number', '>', $examNumber);
                        });
                })
                ->orderByRaw('CAST(s.exam_number AS UNSIGNED) ASC')
                ->orderBy('s.exam_number', 'asc')
                ->select('s.id')
                ->first();
        } else {
            $row = DB::table('main_table')
                ->where(function ($query) use ($examNumber): void {
                    $query->whereRaw('CAST(`الرقم الامتحاني` AS UNSIGNED) > CAST(? AS UNSIGNED)', [$examNumber])
                        ->orWhere(function ($inner) use ($examNumber): void {
                            $inner->whereRaw('CAST(`الرقم الامتحاني` AS UNSIGNED) = CAST(? AS UNSIGNED)', [$examNumber])
                                ->whereRaw('`الرقم الامتحاني` > ?', [$examNumber]);
                        });
                })
                ->orderByRaw('CAST(`الرقم الامتحاني` AS UNSIGNED) ASC')
                ->orderBy('الرقم الامتحاني', 'asc')
                ->select('id')
                ->first();
        }

        return $row ? (int) $row->id : null;
    }

    public function findNextStudentIdInList(int $currentStudentId, array $filters): ?int
    {
        $ids = $this->listIdsWithFilters($filters);
        $index = array_search($currentStudentId, $ids, true);
        if ($index === false) {
            return null;
        }

        return $ids[$index + 1] ?? null;
    }

    public function findPreviousStudentIdInList(int $currentStudentId, array $filters): ?int
    {
        $ids = $this->listIdsWithFilters($filters);
        $index = array_search($currentStudentId, $ids, true);
        if ($index === false || $index === 0) {
            return null;
        }

        return $ids[$index - 1];
    }

    public function existsExamNumber(string $examNumber): bool
    {
        $examNumber = trim($examNumber);
        if ($examNumber === '') {
            return false;
        }
        if ($this->useNormalizedSchema()) {
            return DB::table('students')->where('exam_number', $examNumber)->exists();
        }

        return DB::table('main_table')->where('الرقم الامتحاني', $examNumber)->exists();
    }

    public function findByExamNumber(string $examNumber): ?object
    {
        $examNumber = trim($examNumber);
        if ($examNumber === '') {
            return null;
        }
        if ($this->useNormalizedSchema()) {
            $row = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->where('s.exam_number', $examNumber)
                ->selectRaw("s.id, s.exam_number, CONCAT_WS(' ', p.first_name, p.father_name, p.grandfather_name, p.surname) AS full_name, COALESCE(b.name_ar, '') AS branch, COALESCE(m.name_ar, '') AS major, COALESCE(y.year_label, '') AS academic_year")
                ->first();

            return $row ? (object) [
                'id' => (int) $row->id,
                'exam_number' => (string) $row->exam_number,
                'full_name' => trim((string) $row->full_name),
                'branch' => trim((string) $row->branch),
                'major' => trim((string) $row->major),
                'academic_year' => trim((string) $row->academic_year),
            ] : null;
        }
        $row = DB::table('main_table')
            ->where('الرقم الامتحاني', $examNumber)
            ->selectRaw("id, `الرقم الامتحاني` AS exam_number, CONCAT_WS(' ', `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`) AS full_name, TRIM(`الفرع`) AS branch, TRIM(`الاختصاص`) AS major, `العام الدراسي` AS academic_year")
            ->first();

        return $row ? (object) [
            'id' => (int) $row->id,
            'exam_number' => (string) $row->exam_number,
            'full_name' => trim((string) $row->full_name),
            'branch' => trim((string) $row->branch),
            'major' => trim((string) $row->major),
            'academic_year' => trim((string) $row->academic_year),
        ] : null;
    }

    public function listRepeatersReport(array $filters): array
    {
        $filterLists = $this->getFilterListsFromCache();
        if (empty($filters['year'])) {
            return [
                'groups' => [],
                'stats' => ['total_repeaters' => 0],
                'filters' => [
                    'academicYears' => $filterLists['academicYears'],
                    'branches' => $filterLists['branches'],
                    'majors' => $filterLists['majors'],
                    'genders' => $filterLists['genders'],
                ],
            ];
        }

        $groups = $this->useNormalizedSchema()
            ? $this->listRepeatersReportNormalized($filters)
            : $this->listRepeatersReportLegacy($filters);

        $total = 0;
        foreach ($groups as $group) {
            $total += (int) ($group['count'] ?? 0);
        }

        return [
            'groups' => $groups,
            'stats' => ['total_repeaters' => $total],
            'filters' => [
                'academicYears' => $filterLists['academicYears'],
                'branches' => $filterLists['branches'],
                'majors' => $filterLists['majors'],
                'genders' => $filterLists['genders'],
            ],
        ];
    }

    /** @return list<array{branch:string,major:string,students:list<array{id:int,exam_number:string,full_name:string,subjects:list<array{subject:string,score:string}>,total:string,average:string,result:string}>,count:int}> */
    private function listRepeatersReportNormalized(array $filters): array
    {
        $base = DB::table('students as s')
            ->join('student_personal as p', 'p.student_id', '=', 's.id')
            ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
            ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
            ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
            ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
            ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
            ->whereIn('rt.name_ar', ['معيد', 'معيده', 'معيدة']);
        $this->applyListFiltersNormalized($base, $filters);

        $rows = $base
            ->selectRaw("
                s.id,
                s.exam_number,
                CONCAT_WS(' ', p.first_name, p.father_name, p.grandfather_name, p.surname) AS full_name,
                b.name_ar AS branch,
                m.name_ar AS major,
                rt.name_ar AS result,
                a.total,
                a.average
            ")
            ->orderBy('b.name_ar')
            ->orderBy('m.name_ar')
            ->orderByRaw('CAST(s.exam_number AS UNSIGNED) ASC')
            ->orderBy('s.exam_number', 'asc')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $studentIds = $rows->pluck('id')->map(static fn ($id) => (int) $id)->values()->all();
        $subjectRows = DB::table('student_grades as g')
            ->join('major_subjects as ms', 'ms.id', '=', 'g.major_subject_id')
            ->join('subjects as sub', 'sub.id', '=', 'ms.subject_id')
            ->whereIn('g.student_id', $studentIds)
            ->orderBy('ms.sort_order')
            ->selectRaw('g.student_id, sub.name_ar AS subject_name, g.score')
            ->get();

        $subjectsByStudent = [];
        foreach ($subjectRows as $sr) {
            $sid = (int) $sr->student_id;
            $subjectsByStudent[$sid] ??= [];
            $subjectsByStudent[$sid][] = [
                'subject' => trim((string) ($sr->subject_name ?? '')),
                'score' => $sr->score !== null && $sr->score !== '' ? (string) $sr->score : '',
            ];
        }

        $groupByAll = empty($filters['branch']) && empty($filters['major']);
        $grouped = [];
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $branch = trim((string) ($row->branch ?? ''));
            $major = trim((string) ($row->major ?? ''));
            $key = $groupByAll ? ($branch.'||'.$major) : '__single__';
            $grouped[$key] ??= [
                'branch' => $groupByAll ? $branch : trim((string) ($filters['branch'] ?? $branch)),
                'major' => $groupByAll ? $major : trim((string) ($filters['major'] ?? $major)),
                'students' => [],
                'count' => 0,
            ];
            $subjects = $subjectsByStudent[$id] ?? [];
            $grouped[$key]['students'][] = [
                'id' => $id,
                'exam_number' => trim((string) ($row->exam_number ?? '')),
                'full_name' => trim((string) ($row->full_name ?? '')),
                'subjects' => $subjects,
                'total' => (string) $this->sumGradeScores($subjects),
                'average' => trim((string) ($row->average ?? '')),
                'result' => trim((string) ($row->result ?? '')),
            ];
            $grouped[$key]['count']++;
        }

        return array_values($grouped);
    }

    /** @return list<array{branch:string,major:string,students:list<array{id:int,exam_number:string,full_name:string,subjects:list<array{subject:string,score:string}>,total:string,average:string,result:string}>,count:int}> */
    private function listRepeatersReportLegacy(array $filters): array
    {
        $base = DB::table('main_table')->whereIn('النتيجة', ['معيد', 'معيده', 'معيدة']);
        $this->applyListFilters($base, $filters);

        $rows = $base
            ->select('*')
            ->selectRaw("
                id,
                `الرقم الامتحاني` AS exam_number,
                CONCAT_WS(' ', `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`) AS full_name,
                TRIM(`الفرع`) AS branch,
                TRIM(`الاختصاص`) AS major,
                `النتيجة` AS result,
                `المجموع` AS total,
                `المعدل` AS average
            ")
            ->orderByRaw('TRIM(`الفرع`) ASC, TRIM(`الاختصاص`) ASC')
            ->orderByRaw('CAST(`الرقم الامتحاني` AS UNSIGNED) ASC')
            ->orderBy('الرقم الامتحاني', 'asc')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $gradeColumns = Config::get('grades_catalog.grade_columns', []);
        $groupByAll = empty($filters['branch']) && empty($filters['major']);
        $grouped = [];
        foreach ($rows as $row) {
            $branch = trim((string) ($row->branch ?? ''));
            $major = trim((string) ($row->major ?? ''));
            $key = $groupByAll ? ($branch.'||'.$major) : '__single__';
            $subjects = [];
            foreach ($gradeColumns as $col) {
                $v = $row->{$col} ?? null;
                if ($v === null || $v === '') {
                    continue;
                }
                $subjects[] = ['subject' => (string) $col, 'score' => is_numeric($v) ? (string) (int) round((float) $v) : trim((string) $v)];
            }
            $grouped[$key] ??= [
                'branch' => $groupByAll ? $branch : trim((string) ($filters['branch'] ?? $branch)),
                'major' => $groupByAll ? $major : trim((string) ($filters['major'] ?? $major)),
                'students' => [],
                'count' => 0,
            ];
            $grouped[$key]['students'][] = [
                'id' => (int) $row->id,
                'exam_number' => trim((string) ($row->exam_number ?? '')),
                'full_name' => trim((string) ($row->full_name ?? '')),
                'subjects' => $subjects,
                'total' => (string) $this->sumGradeScores($subjects),
                'average' => trim((string) ($row->average ?? '')),
                'result' => trim((string) ($row->result ?? '')),
            ];
            $grouped[$key]['count']++;
        }

        return array_values($grouped);
    }

    public function getStudentDocumentInfo(int $id): ?StudentDocumentInfo
    {
        if ($this->useNormalizedSchema()) {
            $hasLockedColumn = Schema::hasColumn('student_academic', 'subjects_completed');
            $select = 's.exam_number, p.first_name, p.father_name, p.grandfather_name, p.surname, p.gender, p.birth_date, p.birth_place, p.mother_full_name, b.name_ar AS branch, m.name_ar AS specialization, y.year_label AS academic_year, rt.name_ar AS result, a.round, a.last_school, a.middle_doc_number, a.middle_doc_date, a.issuing_authority, a.page_number, a.enrollment_number';
            if ($hasLockedColumn) {
                $select .= ', a.subjects_completed';
            }
            $row = DB::table('students as s')
                ->join('student_personal as p', 'p.student_id', '=', 's.id')
                ->leftJoin('student_academic as a', 'a.student_id', '=', 's.id')
                ->leftJoin('branches as b', 'b.id', '=', 'a.branch_id')
                ->leftJoin('majors as m', 'm.id', '=', 'a.major_id')
                ->leftJoin('academic_years as y', 'y.id', '=', 'a.academic_year_id')
                ->leftJoin('result_types as rt', 'rt.id', '=', 'a.result_type_id')
                ->where('s.id', $id)
                ->selectRaw($select)
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
                pageNumber: $trim($row->page_number ?? ''),
                enrollmentNumber: $trim($row->enrollment_number ?? ''),
                lockedSubjectsCompleted: $this->decodeLockedSubjects($hasLockedColumn ? ($row->subjects_completed ?? null) : null),
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
            pageNumber: $trim($row->{'رقم الصفحة'} ?? ''),
            enrollmentNumber: $trim($row->{'رقم القيد'} ?? ''),
            lockedSubjectsCompleted: $this->decodeLockedSubjects($row->{'الدروس التي أكمل بها'} ?? null),
        );
    }

    /**
     * فك تشفير قائمة الدروس المثبتة المخزّنة كـ JSON.
     * NULL/فراغ = غير مثبتة؛ مصفوفة (قد تكون فارغة) = مثبتة.
     *
     * @return list<string>|null
     */
    private function decodeLockedSubjects(mixed $raw): ?array
    {
        if ($raw === null) {
            return null;
        }
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return null;
        }

        return array_values(array_map(static fn ($v): string => (string) $v, $decoded));
    }

    public function getAcademicYearsForForm(): array
    {
        return AcademicYearOptions::all();
    }
}
