<?php

namespace App\Domain\Student;

/**
 * كتالوج المواد حسب الفرع والاختصاص (Domain port)
 */
interface SubjectCatalogInterface
{
    /**
     * إرجاع قائمة أسماء المواد للفرع والاختصاص المحددين.
     *
     * @return list<string>
     */
    public function getSubjectsFor(string $branch, string $major): array;

    /**
     * خريطة الفرع → الاختصاص → المواد (لنماذج الواجهة).
     *
     * @return array<string, array<string, list<string>>>
     */
    public function allByBranchAndMajor(): array;
}
