<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع عمود الجهة المعنونة إليها لقبول نص طويل.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('records')) {
            return;
        }

        if (Schema::hasColumn('records', 'addressee')) {
            DB::statement('ALTER TABLE `records` MODIFY `addressee` TEXT NULL');
        }

        if (Schema::hasColumn('records', 'الجهه المعنونه اليها')) {
            DB::statement('ALTER TABLE `records` MODIFY `الجهه المعنونه اليها` TEXT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('records')) {
            return;
        }

        if (Schema::hasColumn('records', 'addressee')) {
            DB::statement('ALTER TABLE `records` MODIFY `addressee` VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('records', 'الجهه المعنونه اليها')) {
            DB::statement('ALTER TABLE `records` MODIFY `الجهه المعنونه اليها` VARCHAR(255) NULL');
        }
    }
};
