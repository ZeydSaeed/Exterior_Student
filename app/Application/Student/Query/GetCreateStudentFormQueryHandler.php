<?php

namespace App\Application\Student\Query;

use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

/**
 * استعلام بيانات نموذج إضافة طالب (CQRS — Query).
 */
final class GetCreateStudentFormQueryHandler
{
    public function __construct(
        private StudentQueryRepository $repository,
        private SubjectCatalogInterface $subjectCatalog,
    ) {}

    /**
     * @return array{academicYears: list<string>, subjectsByBranchMajor: array<string, array<string, list<string>>>}
     */
    public function handle(): array
    {
        return [
            'academicYears' => $this->repository->getAcademicYearsForForm(),
            'subjectsByBranchMajor' => $this->subjectCatalog->allByBranchAndMajor(),
        ];
    }
}
