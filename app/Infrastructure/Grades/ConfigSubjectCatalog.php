<?php

namespace App\Infrastructure\Grades;

use App\Domain\Student\SubjectCatalogInterface;
use Illuminate\Support\Facades\Config;

/**
 * تنفيذ كتالوج المواد من ملف الإعدادات
 */
final class ConfigSubjectCatalog implements SubjectCatalogInterface
{
    public function getSubjectsFor(string $branch, string $major): array
    {
        $branch = trim($branch);
        $major = trim($major);
        $catalog = Config::get('grades_catalog.catalog', []);

        if ($branch === '' || $major === '') {
            return [];
        }

        $branchMajors = $catalog[$branch] ?? null;
        if ($branchMajors === null) {
            return [];
        }

        $subjectsKey = $branchMajors[$major] ?? null;
        if ($subjectsKey === null) {
            return [];
        }

        $subjects = Config::get("grades_catalog.{$subjectsKey}", []);
        return is_array($subjects) ? array_values($subjects) : [];
    }
}
