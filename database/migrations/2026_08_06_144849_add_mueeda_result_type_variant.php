<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة صيغة «معيدة» إلى جانب «معيده» ضمن أنواع النتيجة.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('result_types')) {
            return;
        }

        $maxSort = (int) (DB::table('result_types')->max('sort_order') ?? 0);
        $now = now();

        DB::table('result_types')->updateOrInsert(
            ['name_ar' => 'معيدة'],
            [
                'sort_order' => $maxSort + 1,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('result_types')) {
            return;
        }

        DB::table('result_types')->where('name_ar', 'معيدة')->delete();
    }
};
