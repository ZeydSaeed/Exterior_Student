<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students_import_temp', function (Blueprint $table): void {
            $table->id();
            $table->string('import_batch_id', 64)->index();
            $table->unsignedInteger('row_index');
            $table->string('exam_number', 255)->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('father', 255)->nullable();
            $table->string('grandfather', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('mother', 255)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 255)->nullable();
            $table->string('gender', 64)->nullable();
            $table->string('branch', 255)->nullable();
            $table->string('major', 255)->nullable();
            $table->string('academic_year', 64)->nullable();
            $table->string('last_school', 500)->nullable();
            $table->string('document_number', 255)->nullable();
            $table->date('document_date')->nullable();
            $table->string('issue_place', 500)->nullable();
            $table->string('status', 32)->default('pending'); // pending | valid | failed
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students_import_temp');
    }
};
