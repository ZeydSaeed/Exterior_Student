<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة student_id لجدول certificate والربط بـ main_table بدلاً من الاعتماد على exam_number فقط.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('certificate')) {
            return;
        }

        if (! Schema::hasColumn('certificate', 'student_id')) {
            Schema::table('certificate', function (Blueprint $table): void {
                $table->unsignedBigInteger('student_id')->nullable()->after('id');
            });
        }

        if (Schema::hasTable('main_table')) {
            DB::table('certificate')->orderBy('id')->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $studentId = DB::table('main_table')
                        ->where('الرقم الامتحاني', $row->exam_number)
                        ->value('id');
                    if ($studentId !== null) {
                        DB::table('certificate')->where('id', $row->id)->update(['student_id' => $studentId]);
                    }
                }
            });
        }

        $hasIndex = DB::selectOne("
            SELECT 1 FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'certificate' AND index_name = 'certificate_student_id_index'
            LIMIT 1
        ", [DB::getDatabaseName()]);
        if (! $hasIndex) {
            Schema::table('certificate', function (Blueprint $table): void {
                $table->index('student_id');
            });
        }
        // ملاحظة: إضافة FK إلى main_table قد تفشل إذا كان الجدول تراثياً (محرك أو نوع عمود مختلف).
        // يمكن إضافة القيد يدوياً عند توافق البنية: certificate.student_id -> main_table.id ON DELETE CASCADE
    }

    public function down(): void
    {
        if (! Schema::hasTable('certificate')) {
            return;
        }

        Schema::table('certificate', function (Blueprint $table): void {
            $table->dropIndex(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};
