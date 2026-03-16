<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 4: حقول التدقيق created_by, updated_by على الجداول الرئيسية.
 */
return new class extends Migration
{
    private const TABLES = ['students', 'student_personal', 'student_academic', 'records', 'certificate'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable();
                }
            });
        }
        if (Schema::hasTable('users')) {
            foreach (self::TABLES as $tableName) {
                if (! Schema::hasTable($tableName)) {
                    continue;
                }
                $sm = Schema::getConnection()->getSchemaBuilder();
                if (Schema::hasColumn($tableName, 'created_by') && ! $this->hasForeignKey($tableName, 'created_by')) {
                    Schema::table($tableName, function (Blueprint $table): void {
                        $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                    });
                }
                if (Schema::hasColumn($tableName, 'updated_by') && ! $this->hasForeignKey($tableName, 'updated_by')) {
                    Schema::table($tableName, function (Blueprint $table): void {
                        $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
                    });
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->dropForeign(['updated_by']);
                    $table->dropColumn('updated_by');
                }
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
            });
        }
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        $rows = \Illuminate\Support\Facades\DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
            [\Illuminate\Support\Facades\DB::getDatabaseName(), $table, $column]
        );
        return isset($rows[0]);
    }
};
