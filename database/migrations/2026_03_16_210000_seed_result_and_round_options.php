<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * النتيجة: ناجح، ناجحة، ناجحه، راسب، راسبة، معيد، معيده، معيدة، حجب
 * الدور: الاول، الثاني، الثالث، الاول تكميلي، الثاني تكميلي، الثالث تكميلي
 */
return new class extends Migration
{
    private const RESULT_OPTIONS = ['ناجح', 'ناجحة', 'ناجحه', 'راسب', 'راسبة', 'معيد', 'معيده', 'معيدة', 'حجب'];

    private const ROUND_OPTIONS = ['الاول', 'الثاني', 'الثالث', 'الاول تكميلي', 'الثاني تكميلي', 'الثالث تكميلي'];

    public function up(): void
    {
        $this->seedResultTypes();
        $this->createRoundOptionsTable();
    }

    private function seedResultTypes(): void
    {
        if (! Schema::hasTable('result_types')) {
            return;
        }
        foreach (self::RESULT_OPTIONS as $i => $name) {
            DB::table('result_types')->updateOrInsert(
                ['name_ar' => $name],
                ['sort_order' => $i, 'updated_at' => now()]
            );
        }
    }

    private function createRoundOptionsTable(): void
    {
        if (Schema::hasTable('round_options')) {
            return;
        }
        Schema::create('round_options', function (Blueprint $table): void {
            $table->id();
            $table->string('name_ar')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        foreach (self::ROUND_OPTIONS as $i => $name) {
            DB::table('round_options')->insert([
                'name_ar' => $name,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('round_options');
    }
};
