<?php

namespace App\Infrastructure\Persistence;

use App\Domain\StudentNote\StudentNoteCommandRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * كتابة ملاحظات الطالب في جدول student_notes.
 */
final class MySQLStudentNoteCommandRepository implements StudentNoteCommandRepository
{
    public function create(int $studentId, string $body): void
    {
        $this->assertTableExists();

        DB::transaction(function () use ($studentId, $body): void {
            $now = now();
            DB::table('student_notes')->insert([
                'student_id' => $studentId,
                'body' => $body,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function update(int $studentId, int $noteId, string $body): void
    {
        $this->assertTableExists();

        DB::transaction(function () use ($studentId, $noteId, $body): void {
            $updated = DB::table('student_notes')
                ->where('id', $noteId)
                ->where('student_id', $studentId)
                ->update([
                    'body' => $body,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                throw new RuntimeException('Student note not found.', 404);
            }
        });
    }

    public function delete(int $studentId, int $noteId): void
    {
        $this->assertTableExists();

        DB::transaction(function () use ($studentId, $noteId): void {
            $deleted = DB::table('student_notes')
                ->where('id', $noteId)
                ->where('student_id', $studentId)
                ->delete();

            if ($deleted === 0) {
                throw new RuntimeException('Student note not found.', 404);
            }
        });
    }

    private function assertTableExists(): void
    {
        if (! Schema::hasTable('student_notes')) {
            throw new RuntimeException('Student notes table is missing.');
        }
    }
}
