<?php

namespace App\Application\Student\DTO;

use Illuminate\Support\Collection;

/**
 * استجابة صفحة إحصائيات الطلبة.
 */
final class ListStudentStatisticsResponseDTO
{
    /**
     * @param  array{branch:string,major:string,gender:string,year:string,round:string,result:string}  $selectedFilters
     */
    public function __construct(
        public readonly int $totalStudents,
        public readonly array $selectedFilters,
        public readonly Collection $academicYears,
        public readonly Collection $branches,
        public readonly Collection $majors,
        public readonly Collection $genders,
        public readonly Collection $resultOptions,
        public readonly Collection $roundOptions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'totalStudents' => $this->totalStudents,
            'selectedFilters' => $this->selectedFilters,
            'academicYears' => $this->academicYears,
            'branches' => $this->branches,
            'majors' => $this->majors,
            'genders' => $this->genders,
            'resultOptions' => $this->resultOptions,
            'roundOptions' => $this->roundOptions,
        ];
    }
}
