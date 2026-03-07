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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('document_number')->nullable()->comment('رقم الوثيقة');
            $table->date('document_date')->nullable()->comment('تاريخها');
            $table->string('addressee')->nullable()->comment('الجهة المعنونة إليها');
            $table->string('purpose')->nullable()->comment('الغرض من الوثيقة');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('main_table')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
