<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table): void {
            $table->id();
            $table->string('year_label')->unique();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('main_table')) {
            $distinct = DB::table('main_table')
                ->select('العام الدراسي')
                ->whereNotNull('العام الدراسي')
                ->where('العام الدراسي', '!=', '')
                ->distinct()
                ->orderByDesc('العام الدراسي')
                ->pluck('العام الدراسي');
            foreach ($distinct as $label) {
                $label = trim((string) $label);
                if ($label === '' || DB::table('academic_years')->where('year_label', $label)->exists()) {
                    continue;
                }
                $years = $this->parseYearLabel($label);
                DB::table('academic_years')->insert([
                    'year_label' => $label,
                    'start_year' => $years['start'],
                    'end_year' => $years['end'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /** @return array{start: ?int, end: ?int} */
    private function parseYearLabel(string $label): array
    {
        if (preg_match('/^(\d{4})\s*[-–]\s*(\d{4})$/', $label, $m)) {
            return ['start' => (int) $m[1], 'end' => (int) $m[2]];
        }

        return ['start' => null, 'end' => null];
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
