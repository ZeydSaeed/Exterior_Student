<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة حقل الملاحظات النصي الطويل إلى وثائق الطالب.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('records')) {
            return;
        }

        Schema::table('records', function (Blueprint $table): void {
            if (! Schema::hasColumn('records', 'notes') && Schema::hasColumn('records', 'purpose')) {
                $table->text('notes')->nullable()->after('purpose');
            }

            if (! Schema::hasColumn('records', 'الملاحظات') && Schema::hasColumn('records', 'الغرض من الوثيقة')) {
                $table->text('الملاحظات')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('records')) {
            return;
        }

        Schema::table('records', function (Blueprint $table): void {
            if (Schema::hasColumn('records', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('records', 'الملاحظات')) {
                $table->dropColumn('الملاحظات');
            }
        });
    }
};
