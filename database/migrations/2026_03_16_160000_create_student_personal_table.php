<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_personal', function (Blueprint $table): void {
            $table->unsignedBigInteger('student_id')->primary();
            $table->string('first_name')->default('');
            $table->string('father_name')->default('');
            $table->string('grandfather_name')->default('');
            $table->string('surname')->default('');
            $table->string('gender')->default('');
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->default('');
            $table->string('mother_full_name')->default('');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_personal');
    }
};
