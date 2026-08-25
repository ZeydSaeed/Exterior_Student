<?php

namespace App\Application\Record\Query;

use App\Application\Record\DTO\RecordDTO;
use App\Application\Record\DTO\StudentDocumentsPageDTO;
use App\Domain\Record\RecordQueryRepository;
use App\Domain\Student\StudentQueryRepository;

/**
 * معالج استعلام صفحة وثائق الطالب (CQRS — Query).
 */
final class GetStudentDocumentsPageQueryHandler
{
    public function __construct(
        private StudentQueryRepository $studentRepository,
        private RecordQueryRepository $recordRepository
    ) {}

    public function handle(int $studentId, array $filters = []): ?StudentDocumentsPageDTO
    {
        $student = $this->studentRepository->getStudentById($studentId);
        if ($student === null) {
            return null;
        }

        $records = $this->recordRepository->listByStudentId($studentId);
        $recordDTOs = array_map(
            static fn ($r) => new RecordDTO(
                id: $r->id,
                documentNumber: $r->documentNumber,
                documentDate: $r->documentDate,
                addressee: $r->addressee,
                purpose: $r->purpose,
                notes: $r->notes,
            ),
            $records
        );

        $nextStudentId = $this->studentRepository->findNextStudentIdInList($studentId, $filters);
        $previousStudentId = $this->studentRepository->findPreviousStudentIdInList($studentId, $filters);

        return new StudentDocumentsPageDTO(
            studentId: $student->id,
            examNumber: $student->exam_number,
            studentName: $student->full_name,
            branch: $student->branch,
            major: $student->major,
            academicYear: $student->academic_year,
            round: $student->round,
            gender: $student->gender,
            nextStudentId: $nextStudentId,
            previousStudentId: $previousStudentId,
            records: $recordDTOs,
        );
    }
}
