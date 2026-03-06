<?php

namespace App\Application\Certificate\Command;

use App\Domain\Certificate\CertificateSignatureRepository;

/**
 * أمر حفظ إعدادات تواقيع التأييد.
 */
final class SaveCertificateSignatureSettingsCommandHandler
{
    public function __construct(
        private CertificateSignatureRepository $repository
    ) {}

    public function handle(?int $rightEmployeeId, ?int $leftEmployeeId): void
    {
        $this->repository->setSignature('right', $rightEmployeeId);
        $this->repository->setSignature('left', $leftEmployeeId);
    }
}
