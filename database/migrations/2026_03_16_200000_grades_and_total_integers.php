<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * الدرجات والمجموع أعداد صحيحة فقط؛ المعدل يبقى بمرتبتين عشريتين.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_grades')) {
            DB::statement('ALTER TABLE student_grades MODIFY score INT UNSIGNED NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('student_academic')) {
            DB::statement('ALTER TABLE student_academic MODIFY total INT UNSIGNED NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_grades')) {
            DB::statement('ALTER TABLE student_grades MODIFY score DECIMAL(10,2) NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('student_academic')) {
            DB::statement('ALTER TABLE student_academic MODIFY total DECIMAL(10,2) NOT NULL DEFAULT 0');
        }
    }
};
