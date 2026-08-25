<?php

namespace App\Domain\Record;

/**
 * كيان الوثيقة (سجل وثيقة طالب) — Domain Entity
 */
final class Record
{
    public function __construct(
        public readonly int $id,
        public readonly int $studentId,
        public readonly ?string $documentNumber,
        public readonly ?string $documentDate,
        public readonly ?string $addressee,
        public readonly ?string $purpose,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRow(object $row): self
    {
        $date = $row->document_date ?? null;
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        }

        return new self(
            id: (int) ($row->id ?? 0),
            studentId: (int) ($row->student_id ?? 0),
            documentNumber: isset($row->document_number) ? (string) $row->document_number : null,
            documentDate: $date !== null && $date !== '' ? (string) $date : null,
            addressee: isset($row->addressee) ? (string) $row->addressee : null,
            purpose: isset($row->purpose) ? (string) $row->purpose : null,
            notes: isset($row->notes) ? (string) $row->notes : null,
        );
    }
}
