<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * توحيد عرض وفلترة الجنس: تظهر «انثى» دائماً وتطابق «انثى» و«أنثى».
 */
final class GenderFilterVariants
{
    public const FEMALE_LABEL = 'انثى';

    /** @var list<string> */
    public const FEMALE_VARIANTS = ['انثى', 'أنثى'];

    /**
     * توسيع قيمة فلتر الجنس لتشمل الصيغ المتكافئة.
     *
     * @return list<string>
     */
    public static function expand(string $gender): array
    {
        $gender = trim($gender);
        if ($gender === '') {
            return [];
        }

        if (in_array($gender, self::FEMALE_VARIANTS, true)) {
            return self::FEMALE_VARIANTS;
        }

        return [$gender];
    }

    /**
     * تسمية العرض الموحدة (أنثى → انثى).
     */
    public static function displayLabel(string $gender): string
    {
        $gender = trim($gender);
        if ($gender === '') {
            return '';
        }

        if (in_array($gender, self::FEMALE_VARIANTS, true)) {
            return self::FEMALE_LABEL;
        }

        return $gender;
    }

    /**
     * دمج صيغ الأنثى في قائمة خيارات الفلتر وعرض «انثى» مرة واحدة.
     *
     * @param  iterable<int, mixed>  $genders
     * @return Collection<int, string>
     */
    public static function normalizeOptions(iterable $genders): Collection
    {
        $seen = [];
        $out = [];

        foreach ($genders as $gender) {
            $label = self::displayLabel((string) $gender);
            if ($label === '' || isset($seen[$label])) {
                continue;
            }
            $seen[$label] = true;
            $out[] = $label;
        }

        return collect($out)
            ->sortBy(static fn (string $g): int => ['ذكر' => 0, self::FEMALE_LABEL => 1][$g] ?? 2)
            ->values();
    }
}
