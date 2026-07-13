<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة اختصاص «فن الديكور» تحت فرع «فنون تطبيقية» مع ربط مواده.
 */
return new class extends Migration
{
    private const BRANCH_NAME = 'فنون تطبيقية';

    private const MAJOR_NAME = 'فن الديكور';

    public function up(): void
    {
        if (! Schema::hasTable('branches') || ! Schema::hasTable('majors')) {
            return;
        }

        $branchId = DB::table('branches')->where('name_ar', self::BRANCH_NAME)->value('id');
        if ($branchId === null) {
            $now = now();
            $branchId = DB::table('branches')->insertGetId([
                'name_ar' => self::BRANCH_NAME,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $majorId = DB::table('majors')
            ->where('branch_id', $branchId)
            ->where('name_ar', self::MAJOR_NAME)
            ->value('id');

        if ($majorId === null) {
            $now = now();
            $majorId = DB::table('majors')->insertGetId([
                'name_ar' => self::MAJOR_NAME,
                'branch_id' => $branchId,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->seedMajorSubjects((int) $majorId);
    }

    public function down(): void
    {
        if (! Schema::hasTable('majors')) {
            return;
        }

        $branchId = Schema::hasTable('branches')
            ? DB::table('branches')->where('name_ar', self::BRANCH_NAME)->value('id')
            : null;

        $query = DB::table('majors')->where('name_ar', self::MAJOR_NAME);
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }
        $majorId = $query->value('id');
        if ($majorId === null) {
            return;
        }

        if (Schema::hasTable('major_subjects')) {
            DB::table('major_subjects')->where('major_id', $majorId)->delete();
        }

        DB::table('majors')->where('id', $majorId)->delete();
    }

    private function seedMajorSubjects(int $majorId): void
    {
        if (! Schema::hasTable('major_subjects') || ! Schema::hasTable('subjects')) {
            return;
        }

        $subjectNames = Config::get('grades_catalog.subjects_art', []);
        if (! is_array($subjectNames) || $subjectNames === []) {
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
};
