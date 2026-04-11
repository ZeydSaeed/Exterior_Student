<?php

namespace App\Application\Student\Query;

use App\Application\Student\DTO\ListRepeatersReportResponseDTO;
use App\Domain\Student\StudentQueryRepository;

final class ListRepeatersReportQueryHandler
{
    public function __construct(
        private StudentQueryRepository $repository
    ) {}

    public function handle(ListRepeatersReportQuery $query): ListRepeatersReportResponseDTO
    {
        $data = $this->repository->listRepeatersReport($query->filtersForRepository());

        return new ListRepeatersReportResponseDTO(
            groups: $data['groups'] ?? [],
            totalRepeaters: (int) ($data['stats']['total_repeaters'] ?? 0),
            academicYears: $data['filters']['academicYears'],
            branches: $data['filters']['branches'],
            majors: $data['filters']['majors'],
            genders: $data['filters']['genders'],
            selectedYear: $query->year !== null && trim($query->year) !== '' ? trim($query->year) : null,
        );
    }
}
