<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2: جدول certificate_signers (ربط التأييد بالموظفين بدلاً من حقول نصية).
 * certificate: id, student_id, ... (بدون right_employee_name, left_employee_name).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('certificate')) {
            return;
        }

        if (! Schema::hasTable('certificate_signers')) {
            Schema::create('certificate_signers', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('certificate_id');
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->string('position', 32); // right | left
                $table->timestamps();

                $table->foreign('certificate_id')->references('id')->on('certificate')->cascadeOnDelete();
                $table->unique(['certificate_id', 'position'], 'certificate_signers_certificate_position_unique');
            });
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('certificate', 'right_employee_name')) {
            DB::table('certificate')->orderBy('id')->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $certId = (int) $row->id;
                    $rightName = trim((string) ($row->right_employee_name ?? ''));
                    $leftName = trim((string) ($row->left_employee_name ?? ''));
                    if ($rightName !== '') {
                        $empId = DB::table('employees')->where('name', $rightName)->value('id');
                        if ($empId !== null && ! $this->signerExists($certId, 'right')) {
                            DB::table('certificate_signers')->insert([
                                'certificate_id' => $certId,
                                'employee_id' => $empId,
                                'position' => 'right',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    if ($leftName !== '') {
                        $empId = DB::table('employees')->where('name', $leftName)->value('id');
                        if ($empId !== null && ! $this->signerExists($certId, 'left')) {
                            DB::table('certificate_signers')->insert([
                                'certificate_id' => $certId,
                                'employee_id' => $empId,
                                'position' => 'left',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            });
        }

        // إسقاط أعمدة النص بعد الترحيل (التطبيق يستخدم certificate_signers)
        if (Schema::hasColumn('certificate', 'right_employee_name')) {
            Schema::table('certificate', function (Blueprint $table): void {
                $table->dropColumn(['right_employee_name', 'left_employee_name']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('certificate') && ! Schema::hasColumn('certificate', 'right_employee_name')) {
            Schema::table('certificate', function (Blueprint $table): void {
                $table->string('right_employee_name')->nullable()->after('right_title');
                $table->string('left_employee_name')->nullable()->after('left_title');
            });
        }
        Schema::dropIfExists('certificate_signers');
    }

    private function signerExists(int $certificateId, string $position): bool
    {
        return DB::table('certificate_signers')
            ->where('certificate_id', $certificateId)
            ->where('position', $position)
            ->exists();
    }
};
