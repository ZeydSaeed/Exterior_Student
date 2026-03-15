<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة فهارس على main_table لتحسين أداء القائمة والفلترة وحذف الراسبين/المعيدين.
 * الجدول يُفترض أنه موجود (تراثي). إن كان الجدول غير موجود، يمكن تعليق هذا الـ migration أو حذفه.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('main_table')) {
            return;
        }

        Schema::table('main_table', function (Blueprint $table): void {
            $table->index('الرقم الامتحاني', 'main_table_exam_number_idx');
            $table->index('العام الدراسي', 'main_table_academic_year_idx');
            $table->index('النتيجة', 'main_table_result_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('main_table')) {
            return;
        }

        Schema::table('main_table', function (Blueprint $table): void {
            $table->dropIndex('main_table_exam_number_idx');
            $table->dropIndex('main_table_academic_year_idx');
            $table->dropIndex('main_table_result_idx');
        });
    }
};
