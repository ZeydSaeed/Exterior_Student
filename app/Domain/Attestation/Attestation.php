<?php

namespace App\Domain\Attestation;

/**
 * كيان التأييد المحفوظ (سجل تأييد في جدول certificate).
 */
final class Attestation
{
    public function __construct(
        public readonly int $id,
        public readonly string $examNumber,
        public readonly string $type,
        public readonly ?string $date,
        public readonly ?string $number,
        public readonly ?string $issuedTo,
        public readonly ?string $rightTitle,
        public readonly ?string $rightEmployeeName,
        public readonly ?string $leftTitle,
        public readonly ?string $leftEmployeeName,
    ) {}

    public static function fromRow(object $row): self
    {
        $date = $row->date ?? null;
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        }

        return new self(
            id: (int) ($row->id ?? 0),
            examNumber: (string) ($row->exam_number ?? ''),
            type: (string) ($row->type ?? ''),
            date: $date !== null && $date !== '' ? (string) $date : null,
            number: isset($row->number) ? (string) $row->number : null,
            issuedTo: isset($row->issued_to) ? (string) $row->issued_to : null,
            rightTitle: isset($row->right_title) ? (string) $row->right_title : null,
            rightEmployeeName: isset($row->right_employee_name) ? (string) $row->right_employee_name : null,
            leftTitle: isset($row->left_title) ? (string) $row->left_title : null,
            leftEmployeeName: isset($row->left_employee_name) ? (string) $row->left_employee_name : null,
        );
    }
}
