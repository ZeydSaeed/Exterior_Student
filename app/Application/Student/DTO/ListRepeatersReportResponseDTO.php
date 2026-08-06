<?php

namespace App\Application\Student\DTO;

use Illuminate\Support\Collection;

final class ListRepeatersReportResponseDTO
{
    /**
     * @param  list<array{branch:string,major:string,students:list<array{id:int,exam_number:string,full_name:string,subjects:list<array{subject:string,score:string}>,total:string,average:string,result:string}>,count:int,subject_columns:list<string>,subject_repeater_counts:array<string,int>}>  $groups
     */
    public function __construct(
        public readonly array $groups,
        public readonly int $totalRepeaters,
        public readonly Collection $academicYears,
        public readonly Collection $branches,
        public readonly Collection $majors,
        public readonly Collection $genders,
        public readonly ?string $selectedYear,
    ) {}

    public function toArray(): array
    {
        return [
            'groups' => $this->groups,
            'totalRepeaters' => $this->totalRepeaters,
            'academicYears' => $this->academicYears,
            'branches' => $this->branches,
            'majors' => $this->majors,
            'genders' => $this->genders,
            'selectedYear' => $this->selectedYear,
        ];
    }
}
