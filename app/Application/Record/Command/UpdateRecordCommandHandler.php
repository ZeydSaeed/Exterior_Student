<?php

namespace App\Application\Record\Command;

use App\Domain\Record\RecordCommandRepository;

/**
 * أمر تعديل وثيقة طالب (CQRS — Command).
 */
final class UpdateRecordCommandHandler
{
    public function __construct(
        private RecordCommandRepository $repository
    ) {}

    public function handle(
        int $recordId,
        ?string $documentNumber,
        ?string $documentDate,
        ?string $addressee,
        ?string $purpose
    ): void {
        $this->repository->update(
            recordId: $recordId,
            documentNumber: $documentNumber !== null && $documentNumber !== '' ? trim($documentNumber) : null,
            documentDate: $documentDate !== null && $documentDate !== '' ? trim($documentDate) : null,
            addressee: $addressee !== null && $addressee !== '' ? trim($addressee) : null,
            purpose: $purpose !== null && $purpose !== '' ? trim($purpose) : null,
        );
    }
}
