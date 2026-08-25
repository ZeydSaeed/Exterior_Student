<?php

namespace App\Infrastructure\Persistence;

use App\Domain\StudentNote\StudentNote;
use App\Domain\StudentNote\StudentNoteQueryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * قراءة ملاحظات الطالب من جدول student_notes.
 */
final class MySQLStudentNoteQueryRepository implements StudentNoteQueryRepository
{
    private const MAX_NOTES_PER_STUDENT = 500;

    /**
     * @return list<StudentNote>
     */
    public function listByStudentId(int $studentId): array
    {
        if (! Schema::hasTable('student_notes')) {
            return [];
        }

        return DB::table('student_notes')
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->limit(self::MAX_NOTES_PER_STUDENT)
            ->get()
            ->map(static fn (object $row): StudentNote => StudentNote::fromRow($row))
            ->all();
    }
}
