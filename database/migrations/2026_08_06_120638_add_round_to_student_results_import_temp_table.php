<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_results_import_temp')) {
            return;
        }
        if (! Schema::hasColumn('student_results_import_temp', 'round')) {
            Schema::table('student_results_import_temp', function (Blueprint $table): void {
                $table->string('round', 64)->nullable()->after('result');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_results_import_temp')) {
            return;
        }
        if (Schema::hasColumn('student_results_import_temp', 'round')) {
            Schema::table('student_results_import_temp', function (Blueprint $table): void {
                $table->dropColumn('round');
            });
        }
    }
};
