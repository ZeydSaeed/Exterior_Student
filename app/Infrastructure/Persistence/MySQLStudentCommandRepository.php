<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\StudentCommandRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تنفيذ كتابة الطلاب على MySQL (CQRS — Command side).
 * عند وجود جدول students يُستخدم المسار المُطبّع (students، student_personal، student_academic، إلخ).
 * عند حذف main_table وتجزئته لا يُستخدم main_table أبداً.
 */
final class MySQLStudentCommandRepository implements StudentCommandRepository
{
    private function useNormalizedSchema(): bool
    {
        return Schema::hasTable('students') || ! Schema::hasTable('main_table');
    }

    private function studentGradesUsesMajorSubject(): bool
    {
        return Schema::hasColumn('student_grades', 'major_subject_id');
    }

    /**
     * إرجاع معرف الفرع من name_ar؛ إن لم يُوجَد يُدرَج سطر جديد ويُرجع معرفه.
     */
    private function resolveBranchId(string $nameAr): ?int
    {
        $nameAr = trim($nameAr);
        if ($nameAr === '') {
            return null;
        }
        $id = DB::table('branches')->where('name_ar', $nameAr)->value('id');
        if ($id !== null) {
            return (int) $id;
        }
        $now = now();
        DB::table('branches')->insert([
            'name_ar' => $nameAr,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) DB::table('branches')->where('name_ar', $nameAr)->value('id');
    }

    /**
     * إرجاع معرف الاختصاص من name_ar واختيارياً branch_id؛ إن لم يُوجَد يُدرَج سطر جديد.
     */
    private function resolveMajorId(string $nameAr, ?int $branchId): ?int
    {
        $nameAr = trim($nameAr);
        if ($nameAr === '') {
            return null;
        }
        $query = DB::table('majors')->where('name_ar', $nameAr);
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }
        $id = $query->value('id');
        if ($id !== null) {
            return (int) $id;
        }
        $now = now();
        DB::table('majors')->insert([
            'name_ar' => $nameAr,
            'branch_id' => $branchId,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $q = DB::table('majors')->where('name_ar', $nameAr);
        if ($branchId !== null) {
            $q->where('branch_id', $branchId);
        } else {
            $q->whereNull('branch_id');
        }
        $newMajorId = (int) $q->value('id');
        if (Schema::hasTable('major_subjects') && Schema::hasTable('subjects')) {
            $this->seedMajorSubjectsForNewMajor($newMajorId, $nameAr, $branchId);
        }

        return $newMajorId;
    }

    /**
     * عند إدراج اختصاص جديد: ملء major_subjects من config/grades_catalog إن وُجد تطابق.
     */
    private function seedMajorSubjectsForNewMajor(int $majorId, string $majorNameAr, ?int $branchId): void
    {
        if ($branchId === null) {
            return;
        }
        $branchNameAr = DB::table('branches')->where('id', $branchId)->value('name_ar');
        if ($branchNameAr === null) {
            return;
        }
        $catalog = Config::get('grades_catalog.catalog', []);
        if (! is_array($catalog)) {
            return;
        }
        $branchNameAr = trim($branchNameAr);
        $majorNameAr = trim($majorNameAr);
        $groupKey = $catalog[$branchNameAr][$majorNameAr] ?? null;
        if (! is_string($groupKey) || $groupKey === '') {
            return;
        }
        $subjectGroups = [
            'subjects_industrial' => Config::get('grades_catalog.subjects_industrial', []),
            'subjects_management' => Config::get('grades_catalog.subjects_management', []),
            'subjects_account' => Config::get('grades_catalog.subjects_account', []),
            'subjects_agricultral' => Config::get('grades_catalog.subjects_agricultral', []),
            'subjects_computer' => Config::get('grades_catalog.subjects_computer', []),
            'subjects_art' => Config::get('grades_catalog.subjects_art', []),
            'subjects_hotel' => Config::get('grades_catalog.subjects_hotel', []),
        ];
        $subjectNames = $subjectGroups[$groupKey] ?? [];
        if (! is_array($subjectNames) || empty($subjectNames)) {
            return;
        }
        $subjects = DB::table('subjects')->pluck('id', 'name_ar')->all();
        $now = now();
        foreach ($subjectNames as $sortOrder => $subjectNameAr) {
            $subjectNameAr = is_string($subjectNameAr) ? trim($subjectNameAr) : '';
            if ($subjectNameAr === '') {
                continue;
            }
            $subjectId = $subjects[$subjectNameAr] ?? null;
            if ($subjectId === null) {
                continue;
            }
            if (DB::table('major_subjects')->where('major_id', $majorId)->where('subject_id', $subjectId)->exists()) {
                continue;
            }
            DB::table('major_subjects')->insert([
                'major_id' => $majorId,
                'subject_id' => $subjectId,
                'sort_order' => $sortOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * إرجاع معرف العام الدراسي من year_label؛ إن لم يُوجَد يُدرَج سطر جديد.
     */
    private function resolveAcademicYearId(string $yearLabel): ?int
    {
        $yearLabel = trim($yearLabel);
        if ($yearLabel === '') {
            return null;
        }
        $id = DB::table('academic_years')->where('year_label', $yearLabel)->value('id');
        if ($id !== null) {
            return (int) $id;
        }
        $years = $this->parseAcademicYearLabel($yearLabel);
        $now = now();
        DB::table('academic_years')->insert([
            'year_label' => $yearLabel,
            'start_year' => $years['start'],
            'end_year' => $years['end'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) DB::table('academic_years')->where('year_label', $yearLabel)->value('id');
    }

    /** @return array{start: ?int, end: ?int} */
    private function parseAcademicYearLabel(string $label): array
    {
        if (preg_match('/^(\d{4})\s*[-–]\s*(\d{4})$/', trim($label), $m)) {
            return ['start' => (int) $m[1], 'end' => (int) $m[2]];
        }

        return ['start' => null, 'end' => null];
    }

    /**
     * إرجاع معرف الجنس من name_ar (ذكر، أنثى، انثى)؛ إن وُجد جدول genders.
     */
    private function resolveGenderId(string $nameAr): ?int
    {
        if (! Schema::hasTable('genders')) {
            return null;
        }
        $nameAr = trim($nameAr);
        if ($nameAr === '') {
            return null;
        }
        $id = DB::table('genders')->where('name_ar', $nameAr)->value('id');
        if ($id !== null) {
            return (int) $id;
        }
        if ($nameAr === 'انثى') {
            $id = DB::table('genders')->where('name_ar', 'أنثى')->value('id');

            return $id !== null ? (int) $id : null;
        }

        return null;
    }

    private const BASIC_FIELDS = [
        'name_student' => 'اسم الطالب',
        'name_father' => 'اسم الاب',
        'name_grandfather' => 'اسم الجد',
        'name_surname' => 'اللقب',
        'exam_number' => 'الرقم الامتحاني',
        'birth_date' => 'التولد',
        'birth_place' => 'محل الولادة',
        'mother_full_name' => 'اسم الام الكامل',
        'gender' => 'الجنس',
        'branch' => 'الفرع',
        'major' => 'الاختصاص',
        'academic_year' => 'العام الدراسي',
        'result' => 'النتيجة',
        'total' => 'المجموع',
        'average' => 'المعدل',
        'round' => 'الدور',
    ];

    private const CREATE_FIELDS_MAP = [
        'exam_number' => 'الرقم الامتحاني',
        'name_student' => 'اسم الطالب',
        'name_father' => 'اسم الاب',
        'name_grandfather' => 'اسم الجد',
        'name_surname' => 'اللقب',
        'birth_date' => 'التولد',
        'birth_place' => 'محل الولادة',
        'mother_full_name' => 'اسم الام الكامل',
        'gender' => 'الجنس',
        'branch' => 'الفرع',
        'major' => 'الاختصاص',
        'academic_year' => 'العام الدراسي',
        'last_school' => 'اخر مدرسة كان فيها الطالب',
        'middle_doc_number' => 'رقم الوثيقة المتوسطة',
        'middle_doc_date' => 'تاريخها',
        'issuing_authority' => 'جهة الاصدار',
    ];

    public function create(array $data): int
    {
        if ($this->useNormalizedSchema()) {
            return $this->createNormalized($data);
        }

        return DB::transaction(function () use ($data): int {
            $row = [];
            foreach (self::CREATE_FIELDS_MAP as $key => $column) {
                $value = isset($data[$key]) ? trim((string) $data[$key]) : null;
                // الرقم الامتحاني: إذا تُرك فارغاً نضعه 0
                if ($key === 'exam_number') {
                    $row[$column] = $value !== null && $value !== '' ? $value : '0';

                    continue;
                }

                // حقل التولد وحقل تاريخ الوثيقة: إذا تُركا فارغين نضع لهما تاريخاً افتراضياً
                // بدلاً من NULL أو فراغ لتفادي مشاكل تواريخ غير صالحة مع MySQL STRICT.
                if ($key === 'birth_date' || $key === 'middle_doc_date') {
                    $row[$column] = $value !== null && $value !== '' ? $value : '1000-01-01';

                    continue;
                }

                // باقي الحقول: إذا لم تُرسل القيمة أو كانت فارغة، نخزّن فراغاً بدلاً من NULL
                $row[$column] = $value !== null ? $value : '';
            }
            if (empty($row)) {
                throw new \InvalidArgumentException('لا توجد بيانات طالب للإدراج.');
            }

            // تعبئة جميع أعمدة الدرجات بقيم عددية 0 بشكل افتراضي عند إنشاء الطالب
            // لأن أعمدة المواد معرّفة كأعمدة عددية في قاعدة البيانات، و MySQL لا يقبل
            // إدخال فراغ '' في أعمدة من نوع INT عند تفعيل STRICT.
            $gradeColumns = Config::get('grades_catalog.grade_columns', []);
            foreach ($gradeColumns as $subjectColumn) {
                if (! array_key_exists($subjectColumn, $row)) {
                    $row[$subjectColumn] = 0;
                }
            }

            // الحقول المشتقة / الإدارية التي لا تُملأ من نموذج إضافة الطالب
            // نضبط الحقول الرقمية منها إلى 0 والنصية إلى فراغ افتراضياً عند الإنشاء.
            $numericMetaColumns = [
                'المجموع',
                'المعدل',
            ];
            foreach ($numericMetaColumns as $metaColumn) {
                if (! array_key_exists($metaColumn, $row)) {
                    $row[$metaColumn] = 0;
                }
            }

            $stringMetaColumns = [
                'النتيجة',
                'الدور',
                'الجهة المعنون اليها التاييد',
                'منظم التاييد',
                'مدير القسم او الشعبة',
                'المنصب',
            ];
            foreach ($stringMetaColumns as $metaColumn) {
                if (! array_key_exists($metaColumn, $row)) {
                    $row[$metaColumn] = '';
                }
            }

            $nextId = (int) DB::table('main_table')->max('id') + 1;
            $row['id'] = $nextId;
            DB::table('main_table')->insert($row);
            Cache::forget('student_filters.lists');

            return $nextId;
        });
    }

    private function createNormalized(array $data): int
    {
        return (int) DB::transaction(function () use ($data): int {
            $examNumber = isset($data['exam_number']) && trim((string) $data['exam_number']) !== '' ? trim((string) $data['exam_number']) : '0';
            $birthDate = isset($data['birth_date']) && trim((string) $data['birth_date']) !== '' ? trim((string) $data['birth_date']) : null;
            $middleDocDate = isset($data['middle_doc_date']) && trim((string) $data['middle_doc_date']) !== '' ? trim((string) $data['middle_doc_date']) : null;

            $now = now();
            $studentId = (int) DB::table('students')->insertGetId([
                'exam_number' => $examNumber,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $trim = fn ($k) => isset($data[$k]) ? trim((string) $data[$k]) : '';
            $genderId = $this->resolveGenderId($trim('gender'));
            $personalRow = [
                'student_id' => $studentId,
                'first_name' => $trim('name_student'),
                'father_name' => $trim('name_father'),
                'grandfather_name' => $trim('name_grandfather'),
                'surname' => $trim('name_surname'),
                'gender' => $trim('gender'),
                'birth_date' => $birthDate,
                'birth_place' => $trim('birth_place'),
                'mother_full_name' => $trim('mother_full_name'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('student_personal', 'gender_id')) {
                $personalRow['gender_id'] = $genderId;
            }
            DB::table('student_personal')->insert($personalRow);

            $branchId = $this->resolveBranchId($trim('branch'));
            $majorId = $this->resolveMajorId($trim('major'), $branchId);
            $yearId = $this->resolveAcademicYearId($trim('academic_year'));
            // افتراضياً: النتيجة تكون فارغة حتى لا تظهر "ناجح" قبل إدخال درجات حقيقية.
            $resultId = null;

            DB::table('student_academic')->insert([
                'student_id' => $studentId,
                'branch_id' => $branchId,
                'major_id' => $majorId,
                'academic_year_id' => $yearId,
                'result_type_id' => $resultId,
                'last_school' => $trim('last_school'),
                'middle_doc_number' => $trim('middle_doc_number'),
                'middle_doc_date' => $middleDocDate,
                'issuing_authority' => $trim('issuing_authority'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($this->studentGradesUsesMajorSubject()) {
                $majorSubjectIds = DB::table('major_subjects')->where('major_id', $majorId)->orderBy('sort_order')->pluck('id');
                foreach ($majorSubjectIds as $msId) {
                    DB::table('student_grades')->insert([
                        'student_id' => $studentId,
                        'major_subject_id' => $msId,
                        'academic_year_id' => $yearId,
                        'score' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            } else {
                $subjectIds = DB::table('subjects')->pluck('id');
                foreach ($subjectIds as $subjectId) {
                    DB::table('student_grades')->insert([
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'score' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            Cache::forget('student_filters.lists');

            return $studentId;
        });
    }

    public function updateGrades(int $id, array $payload): bool
    {
        if ($this->useNormalizedSchema()) {
            return $this->updateGradesNormalized($id, $payload);
        }

        return DB::transaction(function () use ($id, $payload): bool {
            $data = [];
            $allowedResults = Config::get('grades_catalog.result_options', []);
            $allowedRounds = Config::get('grades_catalog.round_options', []);
            foreach (self::BASIC_FIELDS as $key => $column) {
                if (array_key_exists($key, $payload)) {
                    $v = trim((string) $payload[$key]);
                    if ($key === 'total' && $v !== '' && is_numeric($v)) {
                        $data[$column] = (string) (int) round((float) $v);
                    } elseif ($key === 'result' && ($v === '' || in_array($v, $allowedResults, true))) {
                        $data[$column] = $v;
                    } elseif ($key === 'round' && ($v === '' || in_array($v, $allowedRounds, true))) {
                        $data[$column] = $v;
                    } elseif ($key === 'birth_date') {
                        $data[$column] = $v !== '' ? $v : '1000-01-01';
                    } elseif ($key !== 'result' && $key !== 'round') {
                        $data[$column] = $v;
                    }
                }
            }
            $gradeColumns = Config::get('grades_catalog.grade_columns', []);
            $allowedGrades = array_fill_keys($gradeColumns, true);
            foreach ($payload['grades'] ?? [] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $subject = trim((string) ($row['subject'] ?? ''));
                $score = trim((string) ($row['score'] ?? ''));
                if ($subject !== '' && isset($allowedGrades[$subject])) {
                    $data[$subject] = is_numeric($score) ? (string) (int) round((float) $score) : '0';
                }
            }
            if (empty($data)) {
                return false;
            }
            $affected = DB::table('main_table')->where('id', $id)->update($data);
            if ($affected > 0) {
                Cache::forget('student_filters.lists');
            }

            return $affected > 0;
        });
    }

    private function updateGradesNormalized(int $id, array $payload): bool
    {
        return DB::transaction(function () use ($id, $payload): bool {
            $any = false;
            if (array_intersect_key($payload, array_flip(['name_student', 'name_father', 'name_grandfather', 'name_surname', 'gender', 'birth_date', 'birth_place', 'mother_full_name'])) !== []) {
                $up = [];
                if (array_key_exists('name_student', $payload)) {
                    $up['first_name'] = trim((string) $payload['name_student']);
                }
                if (array_key_exists('name_father', $payload)) {
                    $up['father_name'] = trim((string) $payload['name_father']);
                }
                if (array_key_exists('name_grandfather', $payload)) {
                    $up['grandfather_name'] = trim((string) $payload['name_grandfather']);
                }
                if (array_key_exists('name_surname', $payload)) {
                    $up['surname'] = trim((string) $payload['name_surname']);
                }
                if (array_key_exists('gender', $payload)) {
                    $up['gender'] = trim((string) $payload['gender']);
                }
                if (array_key_exists('birth_date', $payload)) {
                    $bd = trim((string) $payload['birth_date']);
                    $up['birth_date'] = $bd !== '' ? $bd : null;
                }
                if (array_key_exists('birth_place', $payload)) {
                    $up['birth_place'] = trim((string) $payload['birth_place']);
                }
                if (array_key_exists('mother_full_name', $payload)) {
                    $up['mother_full_name'] = trim((string) $payload['mother_full_name']);
                }
                if ($up !== []) {
                    $up['updated_at'] = now();
                    DB::table('student_personal')->where('student_id', $id)->update($up);
                    $any = true;
                }
            }
            if (array_key_exists('exam_number', $payload)) {
                DB::table('students')->where('id', $id)->update(['exam_number' => trim((string) $payload['exam_number']), 'updated_at' => now()]);
                $any = true;
            }
            $acUp = [];
            if (array_key_exists('branch', $payload)) {
                $acUp['branch_id'] = $this->resolveBranchId((string) $payload['branch']);
            }
            $effectiveBranchId = $acUp['branch_id'] ?? null;
            if ($effectiveBranchId === null && array_key_exists('major', $payload)) {
                $existing = DB::table('student_academic')->where('student_id', $id)->value('branch_id');
                $effectiveBranchId = $existing;
            }
            if (array_key_exists('major', $payload)) {
                $acUp['major_id'] = $this->resolveMajorId((string) $payload['major'], $effectiveBranchId);
            }
            if (array_key_exists('academic_year', $payload)) {
                $acUp['academic_year_id'] = $this->resolveAcademicYearId((string) $payload['academic_year']);
            }
            if (array_key_exists('result', $payload)) {
                $resultName = trim((string) $payload['result']);
                $allowedResults = Config::get('grades_catalog.result_options', []);
                if ($resultName !== '' && in_array($resultName, $allowedResults, true)) {
                    $acUp['result_type_id'] = DB::table('result_types')->where('name_ar', $resultName)->value('id');
                }
            }
            foreach (['total', 'average', 'round'] as $k) {
                if (array_key_exists($k, $payload)) {
                    $v = trim((string) $payload[$k]);
                    if ($k === 'total') {
                        $acUp[$k] = ($v !== '' && is_numeric($v))
                            ? (int) round((float) $v)
                            : 0;
                    } elseif ($k === 'average') {
                        $acUp[$k] = ($v !== '' && is_numeric($v))
                            ? (float) $v
                            : 0;
                    } else {
                        $acUp[$k] = $v;
                    }
                }
            }
            if ($acUp !== []) {
                $acUp['updated_at'] = now();
                DB::table('student_academic')->where('student_id', $id)->update($acUp);
                $any = true;
            }
            if ($this->studentGradesUsesMajorSubject()) {
                $ac = DB::table('student_academic')->where('student_id', $id)->first();
                $majorId = $ac->major_id ?? null;
                $academicYearId = $ac->academic_year_id ?? null;
                if ($majorId !== null && $academicYearId !== null) {
                    $subjectToMajorSubject = DB::table('major_subjects as ms')
                        ->join('subjects as sub', 'sub.id', '=', 'ms.subject_id')
                        ->where('ms.major_id', $majorId)
                        ->pluck('ms.id', 'sub.name_ar');
                    foreach ($payload['grades'] ?? [] as $row) {
                        if (! is_array($row)) {
                            continue;
                        }
                        $subject = trim((string) ($row['subject'] ?? ''));
                        $score = trim((string) ($row['score'] ?? ''));
                        $majorSubjectId = $subject !== '' ? ($subjectToMajorSubject[$subject] ?? null) : null;
                        if ($majorSubjectId !== null) {
                            $scoreInt = is_numeric($score) ? (int) round((float) $score) : 0;
                            $scoreInt = max(0, min(100, $scoreInt));
                            DB::table('student_grades')->updateOrInsert(
                                ['student_id' => $id, 'major_subject_id' => $majorSubjectId, 'academic_year_id' => $academicYearId],
                                ['score' => $scoreInt, 'updated_at' => now()]
                            );
                            $any = true;
                        }
                    }
                }
            } else {
                $gradeColumns = Config::get('grades_catalog.grade_columns', []);
                $subjectIds = DB::table('subjects')->pluck('id', 'name_ar');
                foreach ($payload['grades'] ?? [] as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $subject = trim((string) ($row['subject'] ?? ''));
                    $score = trim((string) ($row['score'] ?? ''));
                    if ($subject !== '' && isset($subjectIds[$subject])) {
                        DB::table('student_grades')->updateOrInsert(
                            ['student_id' => $id, 'subject_id' => $subjectIds[$subject]],
                            ['score' => is_numeric($score) ? (int) round((float) $score) : 0, 'updated_at' => now()]
                        );
                        $any = true;
                    }
                }
            }
            if ($any) {
                Cache::forget('student_filters.lists');
            }

            return $any;
        });
    }

    public function deleteStudent(int $id): bool
    {
        $table = $this->useNormalizedSchema() ? 'students' : 'main_table';

        return DB::transaction(function () use ($id, $table): bool {
            $affected = DB::table($table)->where('id', $id)->delete();
            if ($affected > 0) {
                Cache::forget('student_filters.lists');
            }

            return $affected > 0;
        });
    }

    public function deleteStudentsByIds(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $table = $this->useNormalizedSchema() ? 'students' : 'main_table';

        return (int) DB::transaction(function () use ($ids, $table): int {
            $affected = DB::table($table)->whereIn('id', $ids)->delete();
            if ($affected > 0) {
                Cache::forget('student_filters.lists');
            }

            return $affected;
        });
    }
}
