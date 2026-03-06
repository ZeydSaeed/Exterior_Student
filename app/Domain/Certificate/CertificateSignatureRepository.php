<?php

namespace App\Domain\Certificate;

/**
 * واجهة إعدادات تواقيع التأييد (يمين / يسار).
 * CQRS: قراءة وكتابة إعدادات منفصلة أو عبر نفس الواجهة.
 */
interface CertificateSignatureRepository
{
    /**
     * إرجاع معرّف الموظف المختار لجهة معينة.
     *
     * @return int|null معرّف الموظف أو null
     */
    public function getEmployeeIdByPosition(string $position): ?int;

    /**
     * تعيين الموظف لجهة التأييد.
     */
    public function setSignature(string $position, ?int $employeeId): void;
}
