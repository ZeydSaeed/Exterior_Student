<?php

namespace App\Domain\Student;

/**
 * واجهة قراءة طالب واحد لصفحة التأييد (CQRS — Read / Query)
 * التنفيذ في Infrastructure.
 */
interface StudentReadRepository
{
    public function findById(int $id): ?StudentCertificate;
}
