<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1: ربط records بـ student_id (مفتاح تقني) والـ FK إلى جدول students بدلاً من main_table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('records') || ! Schema::hasColumn('records', 'student_id')) {
            return;
        }
        if (! Schema::hasTable('students')) {
            return;
        }

        $conn = DB::connection();
        $driver = $conn->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        $table = 'records';
        $fkName = $this->getRecordsStudentIdForeignKeyName();
        if ($fkName !== null) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
        }

        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE");
    }

    public function down(): void
    {
        if (! Schema::hasTable('records') || ! Schema::hasTable('main_table')) {
            return;
        }

        $table = 'records';
        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `records_student_id_foreign`");
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `main_table` (`id`) ON DELETE CASCADE");
    }

    private function getRecordsStudentIdForeignKeyName(): ?string
    {
        $rows = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'records' AND COLUMN_NAME = 'student_id' AND REFERENCED_TABLE_NAME IS NOT NULL",
            [DB::getDatabaseName()]
        );
        return isset($rows[0]->CONSTRAINT_NAME) ? (string) $rows[0]->CONSTRAINT_NAME : null;
    }
};
