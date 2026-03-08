<?php

namespace App\Domain\Attestation;

/**
 * واجهة كتابة التأييدات (CQRS — Command).
 */
interface AttestationCommandRepository
{
    public function create(
        string $examNumber,
        string $type,
        ?string $date,
        ?string $number,
        ?string $issuedTo,
        ?string $rightTitle,
        ?string $rightEmployeeName,
        ?string $leftTitle,
        ?string $leftEmployeeName
    ): void;

    public function update(
        int $id,
        ?string $date,
        ?string $number,
        ?string $issuedTo,
        ?string $rightTitle,
        ?string $rightEmployeeName,
        ?string $leftTitle,
        ?string $leftEmployeeName
    ): void;

    public function delete(int $id): void;
}
