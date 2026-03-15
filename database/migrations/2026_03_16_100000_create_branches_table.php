<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('name_ar')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('main_table')) {
            $distinct = DB::table('main_table')
                ->selectRaw('TRIM(`الفرع`) AS name_ar')
                ->whereNotNull('الفرع')
                ->whereRaw('TRIM(`الفرع`) != ""')
                ->distinct()
                ->orderBy('name_ar')
                ->get();
            $sort = 0;
            foreach ($distinct as $row) {
                DB::table('branches')->insert([
                    'name_ar' => $row->name_ar,
                    'sort_order' => $sort++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
