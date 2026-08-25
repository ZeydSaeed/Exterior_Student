<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ملاحظات الطالب المرتبطة بالسجل الشخصي (منفصلة عن ملاحظات الوثائق).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_notes')) {
            return;
        }

        Schema::create('student_notes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->text('body');
            $table->timestamps();

            if (Schema::hasTable('students')) {
                $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            } elseif (Schema::hasTable('main_table')) {
                $table->foreign('student_id')->references('id')->on('main_table')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_notes');
    }
};
