<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // إصلاح القيم القديمة غير الصالحة في حقل التولد (0000-00-00) إلى تاريخ صالح
        // حتى لا يفشل MySQL عند تعديل بنية الجدول مع تفعيل STRICT / NO_ZERO_DATE.
        DB::table('main_table')
            ->where('التولد', '0000-00-00')
            ->update(['التولد' => '1970-01-01']);

        Schema::table('main_table', function (Blueprint $table): void {
            // السماح بأن يكون الرقم الامتحاني فارغاً (NULL)
            $table->string('الرقم الامتحاني')->nullable()->change();

            // الحقول النصية التي نريدها اختيارية (NULL عند عدم الإدخال)
            $table->string('اخر مدرسة كان فيها الطالب')->nullable()->change();
            $table->string('رقم الوثيقة المتوسطة')->nullable()->change();
            $table->string('جهة الاصدار')->nullable()->change();

            // ملاحظة: نترك نوع حقول التواريخ كما هي في قاعدة البيانات،
            // ونتعامل معها من مستوى التطبيق فقط (التحقق + واجهة الإدخال).
        });
    }

    public function down(): void
    {
        Schema::table('main_table', function (Blueprint $table): void {
            // في الرجوع نعيدها كما كانت (بدون nullable)
            $table->string('الرقم الامتحاني')->nullable(false)->change();
            $table->string('اخر مدرسة كان فيها الطالب')->nullable(false)->change();
            $table->string('رقم الوثيقة المتوسطة')->nullable(false)->change();
            $table->string('جهة الاصدار')->nullable(false)->change();
            $table->date('تاريخها')->nullable(false)->change();
        });
    }
};

