<?php

namespace App\Domain\Attestation;

/**
 * واجهة قراءة التأييدات (CQRS — Query).
 */
interface AttestationQueryRepository
{
    /**
     * إرجاع جميع التأييدات لطالب (حسب student_id من main_table نستخرج exam_number).
     *
     * @return list<Attestation>
     */
    public function listByStudentId(int $studentId): array;

    /**
     * جلب تأييد واحد لطالب محدد، أو null إن لم يوجد أو لا يتبعه.
     */
    public function findByStudentAndId(int $studentId, int $attestationId): ?Attestation;
}
