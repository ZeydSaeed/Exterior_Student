<?php

namespace App\Application\Student\Query;

final class ListRepeatersReportQuery
{
    public function __construct(
        public readonly ?string $branch = null,
        public readonly ?string $major = null,
        public readonly ?string $gender = null,
        public readonly ?string $year = null,
        public readonly ?string $search = null,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            branch: $input['branch'] ?? null,
            major: $input['major'] ?? null,
            gender: $input['gender'] ?? null,
            year: $input['year'] ?? null,
            search: $input['search'] ?? null,
        );
    }

    /** @return array{branch?: string, major?: string, gender?: string, year?: string, search?: string} */
    public function filtersForRepository(): array
    {
        $normalize = static function (?string $v): ?string {
            if ($v === null) {
                return null;
            }
            $t = trim($v);

            return $t === '' ? null : $t;
        };

        return [
            'branch' => $normalize($this->branch),
            'major' => $normalize($this->major),
            'gender' => $normalize($this->gender),
            'year' => $normalize($this->year),
            'search' => $normalize($this->search),
        ];
    }
}
