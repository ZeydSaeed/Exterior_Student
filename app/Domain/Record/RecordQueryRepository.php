<?php

namespace App\Domain\Record;

/**
 * واجهة قراءة وثائق الطلاب (CQRS — Query side).
 */
interface RecordQueryRepository
{
    /**
     * إرجاع جميع وثائق طالب معيّن.
     *
     * @return list<Record>
     */
    public function listByStudentId(int $studentId): array;
}
