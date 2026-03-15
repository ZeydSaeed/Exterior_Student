<?php

namespace App\Application\Student\DTO;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * DTO لنتيجة قائمة الطلاب — بديل آمن ومُكتَب للـ array
 */
final class ListStudentsResponseDTO
{
    public function __construct(
        public readonly LengthAwarePaginator $students,
        public readonly Collection $academicYears,
        public readonly Collection $branches,
        public readonly Collection $majors,
        public readonly Collection $genders,
        public readonly Collection $resultOptions,
        public readonly Collection $roundOptions,
        public readonly ?string $searchPattern = null,
    ) {}

    /** تحويل إلى مصفوفة للـ View (مثلاً view('x', $dto->toArray())) */
    public function toArray(): array
    {
        return [
            'students'       => $this->students,
            'academicYears'  => $this->academicYears,
            'branches'       => $this->branches,
            'majors'         => $this->majors,
            'genders'        => $this->genders,
            'resultOptions'  => $this->resultOptions,
            'roundOptions'   => $this->roundOptions,
            'searchPattern'  => $this->searchPattern,
        ];
    }
}
