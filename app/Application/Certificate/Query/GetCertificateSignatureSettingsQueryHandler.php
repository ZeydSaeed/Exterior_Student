<?php

namespace App\Application\Certificate\Query;

use App\Domain\Certificate\CertificateSignatureRepository;

/**
 * استعلام إعدادات تواقيع التأييد (يمين / يسار).
 */
final class GetCertificateSignatureSettingsQueryHandler
{
    public function __construct(
        private CertificateSignatureRepository $repository
    ) {}

    /**
     * @return array{right: int|null, left: int|null}
     */
    public function handle(): array
    {
        return [
            'right' => $this->repository->getEmployeeIdByPosition('right'),
            'left' => $this->repository->getEmployeeIdByPosition('left'),
        ];
    }
}
