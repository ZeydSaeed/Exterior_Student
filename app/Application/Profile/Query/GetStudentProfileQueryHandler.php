<?php

namespace App\Application\Profile\Query;

use App\Application\Profile\DTO\AttestationDTO;
use App\Application\Profile\DTO\StudentProfileDTO;
use App\Application\Record\DTO\RecordDTO;
use App\Domain\Attestation\AttestationQueryRepository;
use App\Domain\Record\RecordQueryRepository;
use App\Domain\Student\StudentQueryRepository;

/**
 * استعلام السجل الشخصي للطالب (تأييدات + وثائق).
 */
final class GetStudentProfileQueryHandler
{
    public function __construct(
        private StudentQueryRepository $studentRepository,
        private AttestationQueryRepository $attestationRepository,
        private RecordQueryRepository $recordRepository
    ) {}

    public function handle(int $studentId): ?StudentProfileDTO
    {
        $student = $this->studentRepository->getStudentById($studentId);
        if ($student === null) {
            return null;
        }

        $attestations = $this->attestationRepository->listByStudentId($studentId);
        $records = $this->recordRepository->listByStudentId($studentId);

        $attestationDTOs = array_map(
            static fn ($a) => new AttestationDTO(
                id: $a->id,
                type: $a->type,
                date: $a->date,
                number: $a->number,
                issuedTo: $a->issuedTo,
                rightTitle: $a->rightTitle,
                rightEmployeeName: $a->rightEmployeeName,
                leftTitle: $a->leftTitle,
                leftEmployeeName: $a->leftEmployeeName,
            ),
            $attestations
        );

        $recordDTOs = array_map(
            static fn ($r) => new RecordDTO(
                id: $r->id,
                documentNumber: $r->documentNumber,
                documentDate: $r->documentDate,
                addressee: $r->addressee,
                purpose: $r->purpose,
            ),
            $records
        );

        return new StudentProfileDTO(
            studentId: $student->id,
            examNumber: $student->exam_number,
            studentName: $student->full_name,
            attestations: $attestationDTOs,
            records: $recordDTOs,
        );
    }
}
