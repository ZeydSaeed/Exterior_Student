<?php

namespace App\Domain\Student;

/**
 * التحقق من علاقة الاختصاص بالفرع (للاستيراد والتحقق).
 */
interface BranchMajorCatalogInterface
{
    /**
     * هل الاختصاص (major) يتبع الفرع (branch) حسب name_ar.
     */
    public function majorBelongsToBranch(string $majorNameAr, string $branchNameAr): bool;
}
