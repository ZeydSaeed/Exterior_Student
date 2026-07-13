<?php

namespace App\Application\Student\Query;

/**
 * كائن الاستعلام: قائمة الطلاب مع الفلاتر
 */
final class ListStudentsQuery
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

    /** مصفوفة الفلاتر المُطبَّعة للبحث (بدون search pattern للتضليل) */
    public function filtersForRepository(): array
    {
        $search = $this->searchNormalized();

        if ($search !== null) {
            return [
                'branch' => null,
                'major' => null,
                'gender' => null,
                'year' => null,
                'round' => null,
                'result' => null,
                'search' => $search,
            ];
        }

        return [
            'branch' => $this->branch,
            'major' => $this->major,
            'gender' => $this->gender,
            'year' => $this->year,
            'round' => $this->round,
            'result' => $this->result,
            'search' => null,
        ];
    }

    public function searchNormalized(): ?string
    {
        if ($this->search === null || trim($this->search) === '') {
            return null;
        }
        $s = trim($this->search);
        $s = preg_replace('/\s+/', ' ', $s);
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $s);
        $s = str_replace(['أ', 'إ', 'آ'], 'ا', $s);
        $s = str_replace('ى', 'ي', $s);

        return mb_strtolower($s, 'UTF-8');
    }
}
