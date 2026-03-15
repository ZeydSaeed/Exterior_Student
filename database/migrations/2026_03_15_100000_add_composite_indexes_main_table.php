<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فهارس مركبة على main_table لتحسين قائمة الطلاب والفلترة وحذف الراسبين/المعيدين.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('main_table')) {
            return;
        }

        Schema::table('main_table', function (Blueprint $table): void {
            $table->index(['العام الدراسي', 'الفرع'], 'main_table_year_branch_idx');
            $table->index(['العام الدراسي', 'النتيجة'], 'main_table_year_result_idx');
            $table->index(['النتيجة', 'العام الدراسي'], 'main_table_result_year_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('main_table')) {
            return;
        }

        Schema::table('main_table', function (Blueprint $table): void {
            $table->dropIndex('main_table_year_branch_idx');
            $table->dropIndex('main_table_year_result_idx');
            $table->dropIndex('main_table_result_year_idx');
        });
    }
};
