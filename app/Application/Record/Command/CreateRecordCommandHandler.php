<?php

namespace App\Application\Record\Command;

use App\Domain\Record\RecordCommandRepository;

/**
 * أمر إضافة وثيقة جديدة لطالب (CQRS — Command).
 */
final class CreateRecordCommandHandler
{
    public function __construct(
        private RecordCommandRepository $repository
    ) {}

    public function handle(
        int $studentId,
        ?string $documentNumber,
        ?string $documentDate,
        ?string $addressee,
        ?string $purpose,
        ?string $notes = null
    ): void {
        $this->repository->create(
            studentId: $studentId,
            documentNumber: $documentNumber !== null && $documentNumber !== '' ? trim($documentNumber) : null,
            documentDate: $documentDate !== null && $documentDate !== '' ? trim($documentDate) : null,
            addressee: $addressee !== null && $addressee !== '' ? trim($addressee) : null,
            purpose: $purpose !== null && $purpose !== '' ? trim($purpose) : null,
            notes: $notes !== null && $notes !== '' ? trim($notes) : null,
        );
    }
}
