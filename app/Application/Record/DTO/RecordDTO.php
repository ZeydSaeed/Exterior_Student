<?php

namespace App\Application\Record\DTO;

/**
 * DTO لعرض وثيقة واحدة.
 */
final class RecordDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $documentNumber,
        public readonly ?string $documentDate,
        public readonly ?string $addressee,
        public readonly ?string $purpose,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'document_number' => $this->documentNumber,
            'document_date' => $this->documentDate,
            'addressee' => $this->addressee,
            'purpose' => $this->purpose,
        ];
    }
}
