<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees') && ! Schema::hasColumn('employees', 'workstation_id')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->string('workstation_id', 36)->nullable()->index();
            });
        }

        if (Schema::hasTable('employees')) {
            $this->dropIndexIfExists('employees', 'employees_name_type_unique');
            $this->ensureUniqueIndex('employees', 'employees_ws_name_type_unique', ['workstation_id', 'name', 'type']);
        }

        if (Schema::hasTable('certificate_signatures') && ! Schema::hasColumn('certificate_signatures', 'workstation_id')) {
            Schema::table('certificate_signatures', function (Blueprint $table): void {
                $table->string('workstation_id', 36)->nullable()->index();
            });
        }

        if (Schema::hasTable('certificate_signatures')) {
            $this->dropIndexIfExists('certificate_signatures', 'certificate_signatures_position_unique');
            $this->ensureUniqueIndex('certificate_signatures', 'certificate_signatures_ws_position_unique', ['workstation_id', 'position']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('certificate_signatures')) {
            $this->dropIndexIfExists('certificate_signatures', 'certificate_signatures_ws_position_unique');
            if (Schema::hasColumn('certificate_signatures', 'workstation_id')) {
                Schema::table('certificate_signatures', function (Blueprint $table): void {
                    $table->dropColumn('workstation_id');
                    $table->unique('position');
                });
            }
        }

        if (Schema::hasTable('employees')) {
            $this->dropIndexIfExists('employees', 'employees_ws_name_type_unique');
            if (Schema::hasColumn('employees', 'workstation_id')) {
                Schema::table('employees', function (Blueprint $table): void {
                    $table->dropColumn('workstation_id');
                });
            }
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function ensureUniqueIndex(string $table, string $indexName, array $columns): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName, $columns): void {
            $blueprint->unique($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName): void {
            $blueprint->dropUnique($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $row = DB::selectOne(
                'SELECT 1 AS ok FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [DB::getDatabaseName(), $table, $indexName]
            );

            return $row !== null;
        }

        if ($driver === 'sqlite') {
            $rows = DB::select('PRAGMA index_list('.$table.')');
            foreach ($rows as $row) {
                $name = (string) ($row->name ?? '');
                if ($name === $indexName) {
                    return true;
                }
            }
        }

        return false;
    }
};
