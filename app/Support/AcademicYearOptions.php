<?php

namespace App\Support;

/**
 * خيارات العام الدراسي بصيغة YYYY-YYYY (مثل 2026-2027).
 */
final class AcademicYearOptions
{
    public const START_YEAR = 1990;

    public const END_YEAR = 2040;

    /**
     * قائمة الأعوام من الأقدم إلى الأحدث.
     *
     * @return list<string>
     */
    public static function all(int $from = self::START_YEAR, int $to = self::END_YEAR): array
    {
        if ($to < $from) {
            return [];
        }

        $years = [];
        for ($year = $from; $year <= $to; $year++) {
            $years[] = self::labelForStartYear($year);
        }

        return $years;
    }

    public static function labelForStartYear(int $startYear): string
    {
        return $startYear.'-'.($startYear + 1);
    }

    /**
     * العام الدراسي الحالي: من أيلول يبدأ العام الجديد.
     */
    public static function current(): string
    {
        $year = (int) date('Y');
        $month = (int) date('n');

        if ($month >= 9) {
            return self::labelForStartYear($year);
        }

        return self::labelForStartYear($year - 1);
    }

    public static function isValid(string $label): bool
    {
        return in_array(trim($label), self::all(), true);
    }
}
