<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ربط student_grades بـ major_subjects و academic_year (تصميم Enterprise).
 * student_grades: student_id, major_subject_id, academic_year_id, score
 * Unique: (student_id, major_subject_id, academic_year_id)
 * CHECK: score 0-100
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_grades') || ! Schema::hasTable('major_subjects')) {
            return;
        }

        if (! Schema::hasColumn('student_grades', 'major_subject_id')) {
            Schema::table('student_grades', function (Blueprint $table): void {
                $table->unsignedBigInteger('major_subject_id')->nullable()->after('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('major_subject_id');
            });
        }

        if (Schema::hasColumn('student_grades', 'subject_id')) {
            $this->migrateData();

            $idx = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'student_grades' AND CONSTRAINT_TYPE = 'UNIQUE' AND CONSTRAINT_NAME LIKE '%student_id%subject_id%' LIMIT 1", [DB::getDatabaseName()]);
            if ($idx && isset($idx->CONSTRAINT_NAME)) {
                Schema::table('student_grades', function (Blueprint $table) use ($idx): void {
                    $table->dropUnique($idx->CONSTRAINT_NAME);
                });
            }
            Schema::table('student_grades', function (Blueprint $table): void {
                $table->dropForeign(['subject_id']);
                $table->dropColumn('subject_id');
            });
        }

        DB::table('student_grades')->whereNull('major_subject_id')->orWhereNull('academic_year_id')->delete();

        DB::statement('ALTER TABLE student_grades MODIFY major_subject_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE student_grades MODIFY academic_year_id BIGINT UNSIGNED NOT NULL');

        $hasNewUnique = DB::selectOne("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'student_grades' AND CONSTRAINT_NAME = 'student_grades_student_major_subject_year_unique' LIMIT 1", [DB::getDatabaseName()]);
        if (! $hasNewUnique) {
            Schema::table('student_grades', function (Blueprint $table): void {
                $table->foreign('major_subject_id')->references('id')->on('major_subjects')->cascadeOnDelete();
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
                $table->unique(['student_id', 'major_subject_id', 'academic_year_id'], 'student_grades_student_major_subject_year_unique');
            });
        }

        try {
            if (Schema::hasTable('student_grades')) {
                DB::statement('ALTER TABLE student_grades ADD CONSTRAINT student_grades_score_range CHECK (score >= 0 AND score <= 100)');
            }
        } catch (\Throwable $e) {
            // CHECK قد لا يكون مدعوماً في إصدارات MySQL الأقدم
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_grades')) {
            return;
        }
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE student_grades DROP CHECK student_grades_score_range');
            } catch (\Throwable $e) {
                // ignore if check doesn't exist
            }
        }
        Schema::table('student_grades', function (Blueprint $table): void {
            $table->dropUnique('student_grades_student_major_subject_year_unique');
            $table->dropForeign(['major_subject_id']);
            $table->dropForeign(['academic_year_id']);
        });
        if (! Schema::hasColumn('student_grades', 'subject_id')) {
            Schema::table('student_grades', function (Blueprint $table): void {
                $table->unsignedBigInteger('subject_id')->after('student_id');
            });
        }
        Schema::table('student_grades', function (Blueprint $table): void {
            $table->dropColumn(['major_subject_id', 'academic_year_id']);
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->unique(['student_id', 'subject_id']);
        });
    }

    private function migrateData(): void
    {
        $academic = DB::table('student_academic')
            ->whereNotNull('major_id')
            ->whereNotNull('academic_year_id')
            ->get()
            ->keyBy('student_id');
        $majorSubjectsByMajor = DB::table('major_subjects')->get()->groupBy('major_id');

        DB::table('student_grades')->whereNotNull('subject_id')->orderBy('id')->chunkById(500, function ($rows) use ($academic, $majorSubjectsByMajor): void {
            foreach ($rows as $row) {
                $ac = $academic->get($row->student_id);
                if ($ac === null) {
                    continue;
                }
                $majorId = $ac->major_id ?? null;
                $academicYearId = $ac->academic_year_id ?? null;
                if ($majorId === null || $academicYearId === null) {
                    continue;
                }
                $msList = $majorSubjectsByMajor->get($majorId);
                $ms = $msList ? $msList->firstWhere('subject_id', $row->subject_id) : null;
                if ($ms === null) {
                    continue;
                }
                DB::table('student_grades')->where('id', $row->id)->update([
                    'major_subject_id' => $ms->id,
                    'academic_year_id' => $academicYearId,
                ]);
            }
        });
    }
};
