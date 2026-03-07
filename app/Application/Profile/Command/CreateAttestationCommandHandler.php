<?php

namespace App\Application\Profile\Command;

use App\Domain\Attestation\AttestationCommandRepository;
use Illuminate\Support\Facades\DB;

/**
 * أمر إنشاء تأييد (حفظ من صفحة التأييد).
 */
final class CreateAttestationCommandHandler
{
    public function __construct(
        private AttestationCommandRepository $repository
    ) {}

    public function handle(
        string $examNumber,
        string $type,
        ?string $date,
        ?string $number,
        ?string $issuedTo,
        ?string $rightTitle,
        ?string $rightEmployeeName,
        ?string $leftTitle,
        ?string $leftEmployeeName
    ): void {
        $this->repository->create(
            examNumber: $examNumber,
            type: $type,
            date: $this->normalizeDate($date),
            number: $number !== null && $number !== '' ? trim($number) : null,
            issuedTo: $this->trim($issuedTo),
            rightTitle: $this->trim($rightTitle),
            rightEmployeeName: $this->trim($rightEmployeeName),
            leftTitle: $this->trim($leftTitle),
            leftEmployeeName: $this->trim($leftEmployeeName),
        );
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        return trim($value);
    }

    private function trim(?string $value): ?string
    {
        return $value !== null && $value !== '' ? trim($value) : null;
    }
}
