<?php

namespace App\Application\Student\Query;

/**
 * استعلام إحصائيات الطلبة حسب فلاتر القائمة.
 */
final class ListStudentStatisticsQuery
{
    public function __construct(
        public readonly ?string $branch = null,
        public readonly ?string $major = null,
        public readonly ?string $gender = null,
        public readonly ?string $year = null,
        public readonly ?string $round = null,
        public readonly ?string $result = null,
        public readonly ?string $search = null,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            branch: $input['branch'] ?? null,
            major: $input['major'] ?? null,
            gender: $input['gender'] ?? null,
            year: $input['year'] ?? null,
            round: $input['round'] ?? null,
            result: $input['result'] ?? null,
            search: $input['search'] ?? null,
        );
    }

    public function filtersForRepository(): array
    {
        return ListStudentsQuery::fromArray([
            'branch' => $this->branch,
            'major' => $this->major,
            'gender' => $this->gender,
            'year' => $this->year,
            'round' => $this->round,
            'result' => $this->result,
            'search' => $this->search,
        ])->filtersForRepository();
    }

    /**
     * @return array{branch:string,major:string,gender:string,year:string,round:string,result:string}
     */
    public function selectedLabels(): array
    {
        return [
            'branch' => trim((string) ($this->branch ?? '')),
            'major' => trim((string) ($this->major ?? '')),
            'gender' => trim((string) ($this->gender ?? '')),
            'year' => trim((string) ($this->year ?? '')),
            'round' => trim((string) ($this->round ?? '')),
            'result' => trim((string) ($this->result ?? '')),
        ];
    }
}
