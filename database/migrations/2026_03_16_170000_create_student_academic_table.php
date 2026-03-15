<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_academic')) {
            Schema::table('student_academic', function (Blueprint $table): void {
                $table->index(['branch_id', 'major_id', 'academic_year_id', 'result_type_id'], 'stu_acad_filter_idx');
            });
            return;
        }
        Schema::create('student_academic', function (Blueprint $table): void {
            $table->unsignedBigInteger('student_id')->primary();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('major_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('result_type_id')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('average', 10, 2)->default(0);
            $table->string('round')->default('');
            $table->string('last_school')->default('');
            $table->string('middle_doc_number')->default('');
            $table->date('middle_doc_date')->nullable();
            $table->string('issuing_authority')->default('');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('major_id')->references('id')->on('majors')->nullOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('result_type_id')->references('id')->on('result_types')->nullOnDelete();

            $table->index(['branch_id', 'major_id', 'academic_year_id', 'result_type_id'], 'stu_acad_filter_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic');
    }
};
