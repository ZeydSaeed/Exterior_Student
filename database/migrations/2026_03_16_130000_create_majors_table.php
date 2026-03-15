<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('majors', function (Blueprint $table): void {
            $table->id();
            $table->string('name_ar');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->unique(['branch_id', 'name_ar']);
        });

        if (Schema::hasTable('main_table') && Schema::hasTable('branches')) {
            $pairs = DB::table('main_table')
                ->selectRaw('TRIM(`الفرع`) AS branch_name, TRIM(`الاختصاص`) AS major_name')
                ->whereNotNull('الفرع')
                ->whereNotNull('الاختصاص')
                ->whereRaw('TRIM(`الفرع`) != ""')
                ->whereRaw('TRIM(`الاختصاص`) != ""')
                ->distinct()
                ->get();
            $seen = [];
            foreach ($pairs as $row) {
                $key = $row->branch_name.'|'.$row->major_name;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $branchId = DB::table('branches')->where('name_ar', $row->branch_name)->value('id');
                DB::table('majors')->insert([
                    'name_ar' => $row->major_name,
                    'branch_id' => $branchId,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('majors');
    }
};
