<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name_ar')->unique();
            $table->string('code', 64)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $columns = Config::get('grades_catalog.grade_columns', []);
        foreach (array_values($columns) as $i => $name) {
            if ($name === '' || ! is_string($name)) {
                continue;
            }
            $exists = DB::table('subjects')->where('name_ar', $name)->exists();
            if (! $exists) {
                DB::table('subjects')->insert([
                    'name_ar' => $name,
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
