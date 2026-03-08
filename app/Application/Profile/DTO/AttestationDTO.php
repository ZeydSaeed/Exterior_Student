<?php

namespace App\Application\Profile\DTO;

final class AttestationDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly ?string $date,
        public readonly ?string $number,
        public readonly ?string $issuedTo,
        public readonly ?string $rightTitle,
        public readonly ?string $rightEmployeeName,
        public readonly ?string $leftTitle,
        public readonly ?string $leftEmployeeName,
    ) {}
}
