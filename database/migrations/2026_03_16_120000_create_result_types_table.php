<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name_ar')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $defaults = ['ناجح', 'راسب', 'معيد'];
        foreach ($defaults as $i => $name) {
            DB::table('result_types')->insert([
                'name_ar' => $name,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('main_table')) {
            $distinct = DB::table('main_table')
                ->selectRaw('TRIM(`النتيجة`) AS name_ar')
                ->whereNotNull('النتيجة')
                ->whereRaw('TRIM(`النتيجة`) != ""')
                ->distinct()
                ->pluck('name_ar');
            foreach ($distinct as $name) {
                $name = trim((string) $name);
                if ($name === '' || in_array($name, $defaults, true)) {
                    continue;
                }
                DB::table('result_types')->insert([
                    'name_ar' => $name,
                    'sort_order' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('result_types');
    }
};
