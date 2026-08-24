<?php

namespace App\Support;

/**
 * توسيع قيمة فلتر النتيجة لتشمل الصيغ المتكافئة ضمن مجموعات الفلترة.
 */
final class ResultFilterVariants
{
    /** @var list<string> */
    public const PASS_VARIANTS = ['ناجح', 'ناجحة', 'ناجحه'];

    /** @var list<string> */
    public const REPEAT_VARIANTS = ['معيد', 'معيدة', 'معيده'];

    /** @var list<string> */
    public const FAIL_VARIANTS = ['راسب', 'راسبة', 'راسبه'];

    /** @var array<string, list<string>> */
    public const FILTER_GROUPS = [
        'ناجحون' => self::PASS_VARIANTS,
        'معيدون' => self::REPEAT_VARIANTS,
        'راسبون' => self::FAIL_VARIANTS,
        'حجب' => ['حجب'],
    ];

    /**
     * @return list<string>
     */
    public static function filterOptions(): array
    {
        return array_keys(self::FILTER_GROUPS);
    }

    /**
     * @return list<string>
     */
    public static function expand(string $result): array
    {
        $result = trim($result);
        if ($result === '') {
            return [];
        }

        if (isset(self::FILTER_GROUPS[$result])) {
            return self::FILTER_GROUPS[$result];
        }

        foreach (self::FILTER_GROUPS as $variants) {
            if (in_array($result, $variants, true)) {
                return $variants;
            }
        }

        return [$result];
    }

    /**
     * يحوّل قيمة الفلتر (مجموعة أو صيغة قديمة) إلى خيار القائمة المنسدلة.
     */
    public static function resolveFilterOption(string $result): string
    {
        $result = trim($result);
        if ($result === '') {
            return '';
        }

        if (isset(self::FILTER_GROUPS[$result])) {
            return $result;
        }

        foreach (self::FILTER_GROUPS as $label => $variants) {
            if (in_array($result, $variants, true)) {
                return $label;
            }
        }

        return $result;
    }
}
