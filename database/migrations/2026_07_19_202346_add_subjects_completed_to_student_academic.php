<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_academic') && ! Schema::hasColumn('student_academic', 'subjects_completed')) {
            Schema::table('student_academic', function (Blueprint $table): void {
                $table->text('subjects_completed')->nullable()->after('enrollment_number')
                    ->comment('الدروس التي أكمل بها — مثبتة من الدور الأول (JSON)؛ NULL = غير مثبتة (تُحسب ديناميكياً)');
            });
        }

        if (Schema::hasTable('main_table') && ! Schema::hasColumn('main_table', 'الدروس التي أكمل بها')) {
            Schema::table('main_table', function (Blueprint $table): void {
                $table->text('الدروس التي أكمل بها')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_academic') && Schema::hasColumn('student_academic', 'subjects_completed')) {
            Schema::table('student_academic', function (Blueprint $table): void {
                $table->dropColumn('subjects_completed');
            });
        }

        if (Schema::hasTable('main_table') && Schema::hasColumn('main_table', 'الدروس التي أكمل بها')) {
            Schema::table('main_table', function (Blueprint $table): void {
                $table->dropColumn('الدروس التي أكمل بها');
            });
        }
    }
};
