<?php

namespace App\Application\Student;

use App\Application\Student\DTO\ListStudentsResponseDTO;
use App\Domain\Student\StudentQueryRepository;

/**
 * حالة الاستخدام: عرض قائمة الطلاب مع الفلاتر والبحث
 */
final class ListStudentsUseCase
{
    public function __construct(
        private StudentQueryRepository $repository
    ) {}

    /**
     * @param array{branch?: string, major?: string, gender?: string, year?: string, search?: string} $input
     */
    public function execute(array $input): ListStudentsResponseDTO
    {
        $filters = [
            'branch' => $input['branch'] ?? null,
            'major'  => $input['major'] ?? null,
            'gender' => $input['gender'] ?? null,
            'year'   => $input['year'] ?? null,
            'search' => isset($input['search']) ? $this->normalizeSearch($input['search']) : null,
        ];

        $projection = $this->repository->listWithFilters($filters);

        return new ListStudentsResponseDTO(
            students: $projection->students,
            academicYears: $projection->academicYears,
            branches: $projection->branches,
            majors: $projection->majors,
            genders: $projection->genders,
            searchPattern: $this->buildHighlightPattern($input['search'] ?? ''),
        );
    }

    private function normalizeSearch(?string $s): string
    {
        if ($s === null || $s === '') {
            return '';
        }
        $s = trim($s);
        $s = preg_replace('/\s+/', ' ', $s);
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $s);
        $s = str_replace(['أ', 'إ', 'آ'], 'ا', $s);
        $s = str_replace('ى', 'ي', $s);
        return mb_strtolower($s, 'UTF-8');
    }

    /** بناء نمط التضليل للعرض (خارج الـ View) */
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
