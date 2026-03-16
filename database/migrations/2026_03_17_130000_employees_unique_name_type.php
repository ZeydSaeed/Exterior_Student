<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3: منع تكرار (name, type) في employees.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }
        $indexName = 'employees_name_type_unique';
        $hasIndex = DB::selectOne(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = 'employees' AND index_name = ? LIMIT 1",
            [DB::getDatabaseName(), $indexName]
        );
        if (! $hasIndex) {
            Schema::table('employees', function (Blueprint $table) use ($indexName): void {
                $table->unique(['name', 'type'], $indexName);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->dropUnique('employees_name_type_unique');
            });
        }
    }
};
