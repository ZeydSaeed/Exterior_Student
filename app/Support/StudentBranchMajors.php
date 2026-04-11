<?php

namespace App\Support;

/**
 * خريطة الفرع ↔ الاختصاص (مرجع واحد للنماذج والفلاتر).
 */
final class StudentBranchMajors
{
    /**
     * @return array<string, list<string>>
     */
    public static function byBranch(): array
    {
        /** @var array<string, list<string>> */
        return config('student_branch_majors.by_branch', []);
    }

    /**
     * @return list<string>
     */
    public static function majorsForBranch(?string $branch): array
    {
        $map = self::byBranch();
        $b = $branch === null ? '' : trim($branch);
        if ($b === '') {
            return self::allMajorsSorted($map);
        }

        return $map[$b] ?? [];
    }

    /**
     * تنسيق subjectObject في صفحة إضافة الطالب (مفاتيح اختصاص → مصفوفات فارغة).
     *
     * @return array<string, array<string, list<mixed>>>
     */
    public static function subjectObjectForJs(): array
    {
        $out = [];
        foreach (self::byBranch() as $branch => $majors) {
            $out[$branch] = array_fill_keys($majors, []);
        }

        return $out;
    }

    /**
     * @param  array<string, list<string>>|null  $map
     * @return list<string>
     */
    private static function allMajorsSorted(?array $map = null): array
    {
        $map ??= self::byBranch();
        $seen = [];
        foreach ($map as $majors) {
            foreach ($majors as $m) {
                $seen[$m] = true;
            }
        }
        $list = array_keys($seen);
        sort($list, SORT_STRING);

        return $list;
    }
}
