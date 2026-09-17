<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('students') || Schema::hasColumn('students', 'exam_number_sort')) {
            return;
        }

        DB::statement('ALTER TABLE students ADD COLUMN exam_number_sort BIGINT GENERATED ALWAYS AS (CAST(exam_number AS UNSIGNED)) STORED');

        Schema::table('students', function (Blueprint $table): void {
            $table->index('exam_number_sort');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('students') || ! Schema::hasColumn('students', 'exam_number_sort')) {
            return;
        }

        Schema::table('students', function (Blueprint $table): void {
            $table->dropIndex(['exam_number_sort']);
            $table->dropColumn('exam_number_sort');
        });
    }
};
