<?php

namespace App\Application\Student\Query;

use App\Application\Student\DTO\ListStudentsResponseDTO;
use App\Domain\Student\StudentQueryRepository;

/**
 * معالج استعلام قائمة الطلاب (CQRS — Query Handler)
 */
final class ListStudentsQueryHandler
{
    public function __construct(
        private StudentQueryRepository $repository
    ) {}

    public function handle(ListStudentsQuery $query): ListStudentsResponseDTO
    {
        $projection = $this->repository->listWithFilters($query->filtersForRepository());

        return new ListStudentsResponseDTO(
            students: $projection->students,
            academicYears: $projection->academicYears,
            branches: $projection->branches,
            majors: $projection->majors,
            genders: $projection->genders,
            resultOptions: $projection->resultOptions,
            roundOptions: $projection->roundOptions,
            searchPattern: $this->buildHighlightPattern($query->search ?? ''),
        );
    }

    private function buildHighlightPattern(string $searchTerm): ?string
    {
        $searchTerm = trim($searchTerm);
        if ($searchTerm === '') {
            return null;
        }
        $n = trim(preg_replace('/\s+/', ' ', $searchTerm));
        $n = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $n);
        $n = str_replace(['أ', 'إ', 'آ'], 'ا', $n);
        $n = str_replace('ى', 'ي', $n);
        $n = mb_strtolower($n, 'UTF-8');
        $pat = '';
        $len = mb_strlen($n);
        for ($i = 0; $i < $len; $i++) {
            $c = mb_substr($n, $i, 1);
            if ($c === 'ا') {
                $pat .= '[اأإآ]';
            } elseif ($c === 'ي') {
                $pat .= '[يى]';
            } else {
                $pat .= preg_quote($c, '/');
            }
        }
        return $pat !== '' ? '/' . $pat . '/u' : null;
    }
}
