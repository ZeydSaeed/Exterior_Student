<?php

namespace App\Domain\StudentNote;

/**
 * واجهة قراءة ملاحظات الطالب (CQRS — Query).
 */
interface StudentNoteQueryRepository
{
    /**
     * @return list<StudentNote>
     */
    public function listByStudentId(int $studentId): array;
}
