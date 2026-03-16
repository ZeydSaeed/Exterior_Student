<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3: جدول genders (مرجع) + gender_id في student_personal؛ round_id في student_academic.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('genders')) {
            Schema::create('genders', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 16)->unique(); // male | female
                $table->string('name_ar')->unique();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
            DB::table('genders')->insert([
                ['id' => 1, 'code' => 'male', 'name_ar' => 'ذكر', 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'code' => 'female', 'name_ar' => 'أنثى', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (Schema::hasTable('student_personal') && ! Schema::hasColumn('student_personal', 'gender_id')) {
            Schema::table('student_personal', function (Blueprint $table): void {
                $table->unsignedBigInteger('gender_id')->nullable()->after('surname');
            });
            $map = ['ذكر' => 1, 'أنثى' => 2];
            foreach ($map as $name => $id) {
                DB::table('student_personal')->where('gender', $name)->update(['gender_id' => $id]);
            }
            Schema::table('student_personal', function (Blueprint $table): void {
                $table->foreign('gender_id')->references('id')->on('genders')->nullOnDelete();
            });
        }

        if (Schema::hasTable('student_academic') && Schema::hasTable('round_options') && ! Schema::hasColumn('student_academic', 'round_id')) {
            Schema::table('student_academic', function (Blueprint $table): void {
                $table->unsignedBigInteger('round_id')->nullable()->after('result_type_id');
            });
            $rounds = DB::table('round_options')->pluck('id', 'name_ar');
            foreach ($rounds as $name => $rid) {
                DB::table('student_academic')->where('round', $name)->update(['round_id' => $rid]);
            }
            Schema::table('student_academic', function (Blueprint $table): void {
                $table->foreign('round_id')->references('id')->on('round_options')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_academic') && Schema::hasColumn('student_academic', 'round_id')) {
            Schema::table('student_academic', function (Blueprint $table): void {
                $table->dropForeign(['round_id']);
                $table->dropColumn('round_id');
            });
        }
        if (Schema::hasTable('student_personal') && Schema::hasColumn('student_personal', 'gender_id')) {
            Schema::table('student_personal', function (Blueprint $table): void {
                $table->dropForeign(['gender_id']);
                $table->dropColumn('gender_id');
            });
        }
        Schema::dropIfExists('genders');
    }
};
