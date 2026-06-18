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
                if (! Schema::hasColumn('student_academic', 'page_number')) {
                    $table->string('page_number', 50)->default('')->after('issuing_authority');
                }
                if (! Schema::hasColumn('student_academic', 'enrollment_number')) {
                    $table->string('enrollment_number', 50)->default('')->after('page_number');
                }
            });
        }

        if (Schema::hasTable('main_table')) {
            Schema::table('main_table', function (Blueprint $table): void {
                if (! Schema::hasColumn('main_table', 'رقم الصفحة')) {
                    $table->string('رقم الصفحة', 50)->default('')->nullable();
                }
                if (! Schema::hasColumn('main_table', 'رقم القيد')) {
                    $table->string('رقم القيد', 50)->default('')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_academic')) {
            Schema::table('student_academic', function (Blueprint $table): void {
                if (Schema::hasColumn('student_academic', 'enrollment_number')) {
                    $table->dropColumn('enrollment_number');
                }
                if (Schema::hasColumn('student_academic', 'page_number')) {
                    $table->dropColumn('page_number');
                }
            });
        }

        if (Schema::hasTable('main_table')) {
            Schema::table('main_table', function (Blueprint $table): void {
                if (Schema::hasColumn('main_table', 'رقم القيد')) {
                    $table->dropColumn('رقم القيد');
                }
                if (Schema::hasColumn('main_table', 'رقم الصفحة')) {
                    $table->dropColumn('رقم الصفحة');
                }
            });
        }
    }
};
