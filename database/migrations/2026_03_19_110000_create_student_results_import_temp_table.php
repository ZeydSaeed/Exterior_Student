<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_results_import_temp', function (Blueprint $table): void {
            $table->id();
            $table->string('import_batch_id', 64)->index();
            $table->unsignedInteger('row_index');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('exam_number', 255)->nullable();
            $table->string('student_name', 500)->nullable();
            $table->string('branch', 255)->nullable();
            $table->string('major', 255)->nullable();
            $table->string('academic_year', 64)->nullable();
            $table->json('subjects_json')->nullable(); // [{subject,score}]
            $table->string('total', 255)->nullable();
            $table->string('average', 255)->nullable();
            $table->string('result', 255)->nullable();
            $table->string('status', 32)->default('pending'); // pending | valid | failed
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_results_import_temp');
    }
};
