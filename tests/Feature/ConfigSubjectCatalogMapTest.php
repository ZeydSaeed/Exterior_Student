<?php

use App\Domain\Student\SubjectCatalogInterface;
use App\Infrastructure\Grades\ConfigSubjectCatalog;

it('returns subject lists grouped by branch and major for the create form', function () {
    $catalog = new ConfigSubjectCatalog;

    $map = $catalog->allByBranchAndMajor();

    expect($map)->toHaveKey('الصناعي')
        ->and($map['الصناعي'])->toHaveKey('سيارات')
        ->and($map['الصناعي']['سيارات'][0] ?? null)->toBe('التربية الاسلامية')
        ->and($map['الصناعي']['سيارات'][1] ?? null)->toBe('اللغة العربية')
        ->and($map['الصناعي']['سيارات'])->toContain('الرياضيات')
        ->and(app(SubjectCatalogInterface::class)->getSubjectsFor('الصناعي', 'سيارات'))
        ->toBe($map['الصناعي']['سيارات'])
        ->and($map)->toHaveKey('الزراعي')
        ->and($map['الزراعي'])->toHaveKey('زراعي')
        ->and($map['الزراعي']['زراعي'][0] ?? null)->toBe('التربية الاسلامية')
        ->and($map['الزراعي']['زراعي'])->not->toContain('القران الكريم والتربية الاسلامية')
        ->and($map['التجاري']['ادارة'][0] ?? null)->toBe('القران الكريم والتربية الاسلامية')
        ->and($map['الحاسوب وتقنية المعلومات']['تجميع وصيانة الحاسوب'] ?? null)->toContain('صيانة الحاسوب')
        ->and($map['الحاسوب وتقنية المعلومات']['تجميع وصيانة الحاسوب'] ?? [])->not->toContain('صيانة وتجميع الحاسوب')
        ->and($map['الحاسوب وتقنية المعلومات']['شبكات الحاسوب'] ?? null)->toContain('صيانة وتجميع الحاسوب')
        ->and($map['التجاري']['ادارة'] ?? [])->toContain('ادارة الانتاج والعمليات')
        ->and($map['التجاري']['ادارة'] ?? [])->not->toContain('ادارة العمليات والانتاج');
});
