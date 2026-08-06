<?php

namespace App\Application\Student\Query;

use App\Application\Student\DTO\ListStudentStatisticsResponseDTO;
use App\Domain\Student\StudentQueryRepository;

/**
 * معالج استعلام إحصائيات الطلبة.
 */
final class ListStudentStatisticsQueryHandler
{
    public function __construct(
        private StudentQueryRepository $repository
    ) {}

    public function handle(ListStudentStatisticsQuery $query): ListStudentStatisticsResponseDTO
    {
        $filters = $query->filtersForRepository();
        $lists = $this->repository->getFilterLists();

        return new ListStudentStatisticsResponseDTO(
            totalStudents: $this->repository->countWithFilters($filters),
            selectedFilters: $query->selectedLabels(),
            academicYears: $lists['academicYears'],
            branches: $lists['branches'],
            majors: $lists['majors'],
            genders: $lists['genders'],
            resultOptions: $lists['resultOptions'],
            roundOptions: $lists['roundOptions'],
        );
    }
}
