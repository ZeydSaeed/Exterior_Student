<?php

namespace App\Application\Student\Query;

use App\Application\Student\DTO\StudentCertificateDTO;
use App\Domain\Student\StudentReadRepository;

/**
 * Use Case: جلب بيانات طالب لعرض صفحة التأييد (قراءة فقط — CQRS Query)
 */
final class GetStudentCertificateQueryHandler
{
    public function __construct(
        private StudentReadRepository $repository
    ) {}

    /**
     * @param array<int, array{type: string, name: string}> $employees الموظفون من الجلسة
     */
    public function handle(int $id, array $employees): StudentCertificateDTO
    {
        $student = $this->repository->findById($id);

        if ($student === null) {
            throw new \RuntimeException('Student not found for certificate.', 404);
        }

        return new StudentCertificateDTO(
            fullName: $student->fullName(),
            examNumber: $student->examNumber(),
            birthDate: $student->birthDate(),
            branch: $student->branch(),
            specialization: $student->specialization(),
            academicYear: $student->academicYear(),
            result: $student->result(),
            round: $student->round(),
            gender: $student->gender(),
            employees: $employees,
        );
    }
}
