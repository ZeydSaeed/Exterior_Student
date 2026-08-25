<?php

namespace App\Domain\Record;

/**
 * واجهة كتابة وثائق الطلاب (CQRS — Command side).
 */
interface RecordCommandRepository
{
    public function create(
        int $studentId,
        ?string $documentNumber,
        ?string $documentDate,
        ?string $addressee,
        ?string $purpose,
        ?string $notes = null
    ): void;

    public function update(
        int $recordId,
        ?string $documentNumber,
        ?string $documentDate,
        ?string $addressee,
        ?string $purpose,
        ?string $notes = null
    ): void;

    public function delete(int $recordId): void;
}
