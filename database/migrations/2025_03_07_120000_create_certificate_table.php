<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificate', function (Blueprint $table) {
            $table->id();
            $table->string('exam_number')->index();
            $table->string('type', 32); // with_grades | without_grades
            $table->date('date')->nullable();
            $table->string('number')->nullable();
            $table->string('issued_to')->nullable();
            $table->string('right_title')->nullable();
            $table->string('right_employee_name')->nullable();
            $table->string('left_title')->nullable();
            $table->string('left_employee_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate');
    }
};
