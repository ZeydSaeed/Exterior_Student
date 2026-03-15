<?php

namespace App\Domain\Attestation;

/**
 * واجهة كتابة التأييدات (CQRS — Command).
 */
interface AttestationCommandRepository
{
    /**
     * @param int $studentId معرف الطالب (FK)
     * @param string $examNumber الرقم الامتحاني للعرض/التوثيق فقط
     */
    public function create(
        int $studentId,
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
