<?php

namespace App\Application\Profile\Command;

use App\Domain\Attestation\AttestationCommandRepository;

/**
 * أمر حذف تأييد.
 */
final class DeleteAttestationCommandHandler
{
    public function __construct(
        private AttestationCommandRepository $repository
    ) {}

    public function handle(int $id): void
    {
        $this->repository->delete($id);
    }
}
