<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('employees', 'table_group')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->unsignedTinyInteger('table_group')->default(1)->after('name');
            });
            DB::table('employees')->where('type', 'manager')->update(['table_group' => 2]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('employees', 'table_group')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->dropColumn('table_group');
            });
        }
    }
};
