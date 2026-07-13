<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تأكيد اختصاص «الاسكان الفندقي» تحت «السياحة والفندقة» وربط مواده المعتمدة.
 */
return new class extends Migration
{
    private const BRANCH_NAME = 'السياحة والفندقة';

    private const MAJOR_NAME = 'الاسكان الفندقي';

    /**
     * @return list<string>
     */
    private function subjectNames(): array
    {
        $fromConfig = Config::get('grades_catalog.subjects_hotel', []);
        if (is_array($fromConfig) && $fromConfig !== []) {
            return array_values(array_filter(array_map(
                static fn ($name) => is_string($name) ? trim($name) : '',
                $fromConfig
            )));
        }

        return [
            'التربية الاسلامية',
            'اللغة العربية',
            'اللغة الانكليزية',
            'المحاسبة الفندقية',
            'ادارة السفر والحجز الالكتروني',
            'الارشاد السياحي',
            'المهارات العملية التدبير الفندقي',
            'المهارات العملية المكتب الامامي',
        ];
    }

    public function up(): void
    {
        if (! Schema::hasTable('branches') || ! Schema::hasTable('majors') || ! Schema::hasTable('subjects')) {
            return;
        }

        $now = now();
        $branchId = DB::table('branches')->where('name_ar', self::BRANCH_NAME)->value('id');
        if ($branchId === null) {
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
            $majorId = DB::table('majors')->insertGetId([
                'name_ar' => self::MAJOR_NAME,
                'branch_id' => $branchId,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $subjectIds = [];
        foreach ($this->subjectNames() as $sortOrder => $subjectName) {
            $subjectId = DB::table('subjects')->where('name_ar', $subjectName)->value('id');
            if ($subjectId === null) {
                $subjectId = DB::table('subjects')->insertGetId([
                    'name_ar' => $subjectName,
                    'code' => null,
                    'sort_order' => 100 + (int) $sortOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $subjectIds[] = [(int) $subjectId, (int) $sortOrder];
        }

        if (! Schema::hasTable('major_subjects')) {
            return;
        }

        DB::table('major_subjects')->where('major_id', $majorId)->delete();

        foreach ($subjectIds as [$subjectId, $sortOrder]) {
            DB::table('major_subjects')->insert([
                'major_id' => $majorId,
                'subject_id' => $subjectId,
                'sort_order' => $sortOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
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

        // لا نحذف الاختصاص في down لأنه قد يكون موجوداً مسبقاً ببيانات طلاب.
        $newSubjects = [
            'المهارات العملية التدبير الفندقي',
            'المهارات العملية المكتب الامامي',
        ];
        if (Schema::hasTable('subjects') && Schema::hasTable('major_subjects')) {
            foreach ($newSubjects as $name) {
                $subjectId = DB::table('subjects')->where('name_ar', $name)->value('id');
                if ($subjectId === null) {
                    continue;
                }
                $stillLinked = DB::table('major_subjects')->where('subject_id', $subjectId)->exists();
                if (! $stillLinked) {
                    DB::table('subjects')->where('id', $subjectId)->delete();
                }
            }
        }
    }
};
