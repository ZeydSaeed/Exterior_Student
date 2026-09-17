<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ترتيب مواد الفرع الصناعي: التربية الاسلامية ثم اللغة العربية.
 * الدرجات مربوطة بـ major_subject_id فلا تتغير قيمها.
 */
return new class extends Migration
{
    private const BRANCH_NAME = 'الصناعي';

    /**
     * @return list<string>
     */
    private function currentIndustrialSubjects(): array
    {
        $fromConfig = Config::get('grades_catalog.subjects_industrial', []);
        if (is_array($fromConfig) && $fromConfig !== []) {
            return $this->normalizeNames($fromConfig);
        }

        return [
            'التربية الاسلامية',
            'اللغة العربية',
            'اللغة الانكليزية',
            'الرياضيات',
            'الطبيعيات',
            'الرسم الصناعي',
            'العلوم الصناعية',
            'التدريب العملي',
        ];
    }

    /**
     * @return list<string>
     */
    private function previousIndustrialSubjects(): array
    {
        return [
            'اللغة العربية',
            'التربية الاسلامية',
            'اللغة الانكليزية',
            'الرياضيات',
            'الطبيعيات',
            'الرسم الصناعي',
            'العلوم الصناعية',
            'التدريب العملي',
        ];
    }

    /**
     * @param  list<mixed>  $names
     * @return list<string>
     */
    private function normalizeNames(array $names): array
    {
        return array_values(array_filter(array_map(
            static fn ($name): string => is_string($name) ? trim($name) : '',
            $names
        )));
    }

    public function up(): void
    {
        $this->applySortOrder($this->currentIndustrialSubjects());
    }

    public function down(): void
    {
        $this->applySortOrder($this->previousIndustrialSubjects());
    }

    /**
     * @param  list<string>  $subjectNames
     */
    private function applySortOrder(array $subjectNames): void
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

        $majorIds = DB::table('majors')->where('branch_id', $branchId)->pluck('id');
        if ($majorIds->isEmpty()) {
            return;
        }

        $subjects = DB::table('subjects')->pluck('id', 'name_ar');
        $now = now();

        foreach ($majorIds as $majorId) {
            foreach ($subjectNames as $sortOrder => $subjectName) {
                $subjectId = $subjects[$subjectName] ?? null;
                if ($subjectId === null) {
                    continue;
                }

                DB::table('major_subjects')
                    ->where('major_id', $majorId)
                    ->where('subject_id', $subjectId)
                    ->update([
                        'sort_order' => $sortOrder,
                        'updated_at' => $now,
                    ]);
            }
        }
    }
};
