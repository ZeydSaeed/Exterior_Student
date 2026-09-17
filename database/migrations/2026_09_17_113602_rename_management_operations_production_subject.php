<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * الفرع التجاري / اختصاص ادارة: استبدال مادة
 * «ادارة العمليات والانتاج» بـ «ادارة الانتاج والعمليات».
 * درجات الطالب تبقى عبر نفس major_subject_id بعد تحديث subject_id.
 */
return new class extends Migration
{
    private const BRANCH_NAME = 'التجاري';

    private const MAJOR_NAME = 'ادارة';

    private const OLD_SUBJECT_NAME = 'ادارة العمليات والانتاج';

    private const NEW_SUBJECT_NAME = 'ادارة الانتاج والعمليات';

    public function up(): void
    {
        $this->remapSubject(self::OLD_SUBJECT_NAME, self::NEW_SUBJECT_NAME);
    }

    public function down(): void
    {
        $this->remapSubject(self::NEW_SUBJECT_NAME, self::OLD_SUBJECT_NAME);
    }

    private function remapSubject(string $fromName, string $toName): void
    {
        if (
            ! Schema::hasTable('major_subjects')
            || ! Schema::hasTable('majors')
            || ! Schema::hasTable('branches')
            || ! Schema::hasTable('subjects')
        ) {
            return;
        }

        $branchId = DB::table('branches')->where('name_ar', self::BRANCH_NAME)->value('id');
        if ($branchId === null) {
            return;
        }

        $majorId = DB::table('majors')
            ->where('branch_id', $branchId)
            ->where('name_ar', self::MAJOR_NAME)
            ->value('id');
        if ($majorId === null) {
            return;
        }

        $fromSubjectId = DB::table('subjects')->where('name_ar', $fromName)->value('id');
        $toSubjectId = DB::table('subjects')->where('name_ar', $toName)->value('id');
        if ($toSubjectId === null) {
            $sortOrder = $fromSubjectId === null
                ? 0
                : (int) DB::table('subjects')->where('id', $fromSubjectId)->value('sort_order');
            $toSubjectId = DB::table('subjects')->insertGetId([
                'name_ar' => $toName,
                'code' => null,
                'sort_order' => $sortOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::transaction(function () use ($majorId, $fromSubjectId, $toSubjectId, $fromName, $toName): void {
            $this->remapMajorSubject((int) $majorId, $fromSubjectId !== null ? (int) $fromSubjectId : null, (int) $toSubjectId);
            $this->replaceSubjectNameInLockedSubjects((int) $majorId, $fromName, $toName);
            $this->replaceSubjectNameInImportTemp($fromName, $toName);
            $this->copyLegacyMainTableScores($fromName, $toName);
        });
    }

    private function remapMajorSubject(int $majorId, ?int $fromSubjectId, int $toSubjectId): void
    {
        $fromMajorSubject = $fromSubjectId === null
            ? null
            : DB::table('major_subjects')
                ->where('major_id', $majorId)
                ->where('subject_id', $fromSubjectId)
                ->first();

        $toMajorSubject = DB::table('major_subjects')
            ->where('major_id', $majorId)
            ->where('subject_id', $toSubjectId)
            ->first();

        if ($fromMajorSubject === null) {
            if ($toMajorSubject === null) {
                DB::table('major_subjects')->insert([
                    'major_id' => $majorId,
                    'subject_id' => $toSubjectId,
                    'sort_order' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return;
        }

        if ($toMajorSubject === null) {
            DB::table('major_subjects')
                ->where('id', $fromMajorSubject->id)
                ->update([
                    'subject_id' => $toSubjectId,
                    'updated_at' => now(),
                ]);

            return;
        }

        if ((int) $fromMajorSubject->id === (int) $toMajorSubject->id) {
            return;
        }

        $this->moveStudentGrades((int) $fromMajorSubject->id, (int) $toMajorSubject->id);
        DB::table('major_subjects')->where('id', $fromMajorSubject->id)->delete();
    }

    private function moveStudentGrades(int $fromMajorSubjectId, int $toMajorSubjectId): void
    {
        if (! Schema::hasTable('student_grades')) {
            return;
        }

        $existing = DB::table('student_grades')
            ->where('major_subject_id', $toMajorSubjectId)
            ->get(['student_id', 'academic_year_id'])
            ->map(static fn ($row): string => $row->student_id.'|'.$row->academic_year_id)
            ->all();
        $existingKeys = array_flip($existing);

        $oldGrades = DB::table('student_grades')
            ->where('major_subject_id', $fromMajorSubjectId)
            ->get();

        foreach ($oldGrades as $grade) {
            $key = $grade->student_id.'|'.$grade->academic_year_id;
            if (isset($existingKeys[$key])) {
                DB::table('student_grades')->where('id', $grade->id)->delete();

                continue;
            }

            DB::table('student_grades')
                ->where('id', $grade->id)
                ->update([
                    'major_subject_id' => $toMajorSubjectId,
                    'updated_at' => now(),
                ]);
            $existingKeys[$key] = true;
        }
    }

    private function replaceSubjectNameInLockedSubjects(int $majorId, string $fromName, string $toName): void
    {
        if (! Schema::hasTable('student_academic') || ! Schema::hasColumn('student_academic', 'subjects_completed')) {
            return;
        }

        $rows = DB::table('student_academic')
            ->where('major_id', $majorId)
            ->whereNotNull('subjects_completed')
            ->get(['student_id', 'subjects_completed']);

        foreach ($rows as $row) {
            $replaced = $this->replaceSubjectNameInJson((string) $row->subjects_completed, $fromName, $toName);
            if ($replaced === null) {
                continue;
            }

            DB::table('student_academic')
                ->where('student_id', $row->student_id)
                ->update([
                    'subjects_completed' => $replaced,
                    'updated_at' => now(),
                ]);
        }

        if (! Schema::hasTable('main_table') || ! Schema::hasColumn('main_table', 'الدروس التي أكمل بها')) {
            return;
        }

        $legacyRows = DB::table('main_table')
            ->where('الفرع', self::BRANCH_NAME)
            ->where('الاختصاص', self::MAJOR_NAME)
            ->whereNotNull('الدروس التي أكمل بها')
            ->get(['id', 'الدروس التي أكمل بها']);

        foreach ($legacyRows as $row) {
            $replaced = $this->replaceSubjectNameInJson((string) $row->{'الدروس التي أكمل بها'}, $fromName, $toName);
            if ($replaced === null) {
                continue;
            }

            DB::table('main_table')
                ->where('id', $row->id)
                ->update(['الدروس التي أكمل بها' => $replaced]);
        }
    }

    private function replaceSubjectNameInImportTemp(string $fromName, string $toName): void
    {
        if (! Schema::hasTable('student_results_import_temp') || ! Schema::hasColumn('student_results_import_temp', 'subjects_json')) {
            return;
        }

        $rows = DB::table('student_results_import_temp')
            ->where('branch', self::BRANCH_NAME)
            ->where('major', self::MAJOR_NAME)
            ->whereNotNull('subjects_json')
            ->get(['id', 'subjects_json']);

        foreach ($rows as $row) {
            $replaced = $this->replaceSubjectNameInJson((string) $row->subjects_json, $fromName, $toName);
            if ($replaced === null) {
                continue;
            }

            DB::table('student_results_import_temp')
                ->where('id', $row->id)
                ->update(['subjects_json' => $replaced]);
        }
    }

    private function copyLegacyMainTableScores(string $fromName, string $toName): void
    {
        if (
            ! Schema::hasTable('main_table')
            || ! Schema::hasColumn('main_table', $fromName)
            || ! Schema::hasColumn('main_table', $toName)
        ) {
            return;
        }

        $rows = DB::table('main_table')
            ->where('الفرع', self::BRANCH_NAME)
            ->where('الاختصاص', self::MAJOR_NAME)
            ->get(['id', $fromName, $toName]);

        foreach ($rows as $row) {
            $fromScore = trim((string) ($row->{$fromName} ?? ''));
            $toScore = trim((string) ($row->{$toName} ?? ''));
            if ($fromScore === '' || $toScore !== '') {
                continue;
            }

            DB::table('main_table')
                ->where('id', $row->id)
                ->update([$toName => $row->{$fromName}]);
        }
    }

    private function replaceSubjectNameInJson(string $json, string $fromName, string $toName): ?string
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return null;
        }

        $changed = false;
        $replaced = $this->replaceSubjectNameInValue($decoded, $fromName, $toName, $changed);
        if (! $changed) {
            return null;
        }

        return json_encode($replaced, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    private function replaceSubjectNameInValue($value, string $fromName, string $toName, bool &$changed)
    {
        if (is_string($value)) {
            if ($value === $fromName) {
                $changed = true;

                return $toName;
            }

            return $value;
        }

        if (! is_array($value)) {
            return $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = $this->replaceSubjectNameInValue($item, $fromName, $toName, $changed);
        }

        return $out;
    }
};
