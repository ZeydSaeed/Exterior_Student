<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * السماح بتخزين درجات نصية (مثل غ / حجب) إلى جانب الدرجات الرقمية.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_grades') || ! Schema::hasColumn('student_grades', 'score')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE student_grades DROP CHECK student_grades_score_range');
        } catch (\Throwable) {
            // القيد قد لا يكون موجوداً على بعض البيئات.
        }

        DB::statement("ALTER TABLE student_grades MODIFY score VARCHAR(50) NOT NULL DEFAULT '0'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_grades') || ! Schema::hasColumn('student_grades', 'score')) {
            return;
        }

        DB::table('student_grades')
            ->whereRaw("score REGEXP '[^0-9.]'")
            ->update(['score' => '0']);

        DB::statement('ALTER TABLE student_grades MODIFY score INT UNSIGNED NOT NULL DEFAULT 0');

        try {
            DB::statement('ALTER TABLE student_grades ADD CONSTRAINT student_grades_score_range CHECK (score >= 0 AND score <= 100)');
        } catch (\Throwable) {
            // تجاهل إن وُجد القيد مسبقاً.
        }
    }
};
