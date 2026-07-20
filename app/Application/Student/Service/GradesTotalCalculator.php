<?php

namespace App\Application\Student\Service;

/**
 * حساب حقل المجموع بدالة الجمع من درجات المواد.
 * مصدر واحد للمنطق في كل النظام (حفظ، عرض، استيراد).
 */
final class GradesTotalCalculator
{
    /**
     * @param  array<int, array{subject?: string, score?: string|int|float|null}>  $grades
     */
    public function sum(array $grades): int
    {
        $total = 0;
        foreach ($grades as $row) {
            if (! is_array($row)) {
                continue;
            }
            $score = trim((string) ($row['score'] ?? ''));
            if ($score !== '' && is_numeric($score)) {
                $total += (int) round((float) $score);
            }
        }

        return $total;
    }
}
