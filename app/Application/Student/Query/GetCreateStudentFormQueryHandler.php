<?php

namespace App\Application\Student\Query;

use App\Domain\Student\StudentQueryRepository;

/**
 * استعلام بيانات نموذج إضافة طالب (CQRS — Query).
 */
final class GetCreateStudentFormQueryHandler
{
    public function __construct(
        private StudentQueryRepository $repository
    ) {}

    /**
     * @return array{academicYears: list<string>}
     */
    public function handle(): array
    {
        $academicYears = $this->repository->getAcademicYearsForForm();

        return [
            'academicYears' => $academicYears,
        ];
    }
}
