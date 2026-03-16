<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * جدول ربط الاختصاص بالمواد (Major → Subjects) من config/grades_catalog.
 * يمنع تسجيل درجات لمواد لا تتبع اختصاص الطالب.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('majors') || ! Schema::hasTable('subjects')) {
            return;
        }

        Schema::create('major_subjects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('major_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('major_id')->references('id')->on('majors')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->unique(['major_id', 'subject_id'], 'major_subjects_major_subject_unique');
        });

        $this->seedFromCatalog();
    }

    public function down(): void
    {
        Schema::dropIfExists('major_subjects');
    }

    private function seedFromCatalog(): void
    {
        $catalog = Config::get('grades_catalog.catalog', []);
        if (! is_array($catalog)) {
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

        $branches = DB::table('branches')->pluck('id', 'name_ar')->all();
        $subjects = DB::table('subjects')->pluck('id', 'name_ar')->all();
        $inserted = [];

        foreach ($catalog as $branchNameAr => $majorsMap) {
            if (! is_array($majorsMap)) {
                continue;
            }
            $branchId = $branches[trim($branchNameAr)] ?? null;
            if ($branchId === null) {
                continue;
            }

            foreach ($majorsMap as $majorNameAr => $groupKey) {
                $majorNameAr = trim($majorNameAr);
                $groupKey = is_string($groupKey) ? trim($groupKey) : '';
                $majorRow = DB::table('majors')->where('branch_id', $branchId)->where('name_ar', $majorNameAr)->first();
                if ($majorRow === null) {
                    continue;
                }
                $subjectNames = $subjectGroups[$groupKey] ?? [];
                if (! is_array($subjectNames)) {
                    continue;
                }

                foreach ($subjectNames as $sortOrder => $subjectNameAr) {
                    $subjectNameAr = is_string($subjectNameAr) ? trim($subjectNameAr) : '';
                    if ($subjectNameAr === '') {
                        continue;
                    }
                    $subjectId = $subjects[$subjectNameAr] ?? null;
                    if ($subjectId === null) {
                        continue;
                    }
                    $key = $majorRow->id.'|'.$subjectId;
                    if (isset($inserted[$key])) {
                        continue;
                    }
                    $inserted[$key] = true;
                    DB::table('major_subjects')->insert([
                        'major_id' => $majorRow->id,
                        'subject_id' => $subjectId,
                        'sort_order' => $sortOrder,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
