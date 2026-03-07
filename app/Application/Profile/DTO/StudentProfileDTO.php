<?php

namespace App\Application\Profile\DTO;

use App\Application\Record\DTO\RecordDTO;

/**
 * DTO لصفحة السجل الشخصي = الطالب + التأييدات + الوثائق.
 *
 * @param list<AttestationDTO> $attestations
 * @param list<RecordDTO>     $records
 */
final class StudentProfileDTO
{
    public function __construct(
        public readonly int $studentId,
        public readonly ?string $examNumber,
        public readonly ?string $studentName,
        public readonly array $attestations,
        public readonly array $records,
    ) {}
}
