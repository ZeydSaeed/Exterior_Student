<?php

use App\Domain\Student\SubjectCatalogInterface;
use App\Infrastructure\Grades\ConfigSubjectCatalog;

it('returns subject lists grouped by branch and major for the create form', function () {
    $catalog = new ConfigSubjectCatalog;

    $map = $catalog->allByBranchAndMajor();

    expect($map)->toHaveKey('الصناعي')
        ->and($map['الصناعي'])->toHaveKey('سيارات')
        ->and($map['الصناعي']['سيارات'])->toContain('اللغة العربية')
        ->and($map['الصناعي']['سيارات'])->toContain('الرياضيات')
        ->and(app(SubjectCatalogInterface::class)->getSubjectsFor('الصناعي', 'سيارات'))
        ->toBe($map['الصناعي']['سيارات']);
});
