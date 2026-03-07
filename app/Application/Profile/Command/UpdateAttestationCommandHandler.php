<?php

namespace App\Application\Profile\Command;

use App\Domain\Attestation\AttestationCommandRepository;

/**
 * أمر تعديل تأييد.
 */
final class UpdateAttestationCommandHandler
{
    public function __construct(
        private AttestationCommandRepository $repository
    ) {}

    public function handle(
        int $id,
        ?string $date,
        ?string $number,
        ?string $issuedTo,
        ?string $rightTitle,
        ?string $rightEmployeeName,
        ?string $leftTitle,
        ?string $leftEmployeeName
    ): void {
        $this->repository->update(
            id: $id,
            date: $date !== null && $date !== '' ? trim($date) : null,
            number: $number !== null && $number !== '' ? trim($number) : null,
            issuedTo: $issuedTo !== null && $issuedTo !== '' ? trim($issuedTo) : null,
            rightTitle: $rightTitle !== null && $rightTitle !== '' ? trim($rightTitle) : null,
            rightEmployeeName: $rightEmployeeName !== null && $rightEmployeeName !== '' ? trim($rightEmployeeName) : null,
            leftTitle: $leftTitle !== null && $leftTitle !== '' ? trim($leftTitle) : null,
            leftEmployeeName: $leftEmployeeName !== null && $leftEmployeeName !== '' ? trim($leftEmployeeName) : null,
        );
    }
}
