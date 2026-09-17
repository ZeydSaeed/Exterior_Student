<?php

use App\Infrastructure\Grades\ConfigSubjectCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('uses islamic education instead of quran for the agricultural catalog', function () {
    $catalog = new ConfigSubjectCatalog;
    $subjects = $catalog->getSubjectsFor('الزراعي', 'زراعي');

    expect($subjects[0] ?? null)->toBe('التربية الاسلامية')
        ->and($subjects)->not->toContain('القران الكريم والتربية الاسلامية')
        ->and($subjects)->toContain('اللغة العربية');
});

it('stores islamic education for the agricultural major in the database', function () {
    if (
        ! Schema::hasTable('branches')
        || ! Schema::hasTable('majors')
        || ! Schema::hasTable('major_subjects')
        || ! Schema::hasTable('subjects')
    ) {
        $this->markTestSkipped('Normalized majors schema is not available.');
    }

    $branchId = DB::table('branches')->where('name_ar', 'الزراعي')->value('id');
    expect($branchId)->not->toBeNull();

    $major = DB::table('majors')
        ->where('branch_id', $branchId)
        ->where('name_ar', 'زراعي')
        ->first();

    expect($major)->not->toBeNull();

    $subjectNames = DB::table('major_subjects as ms')
        ->join('subjects as s', 's.id', '=', 'ms.subject_id')
        ->where('ms.major_id', $major->id)
        ->orderBy('ms.sort_order')
        ->pluck('s.name_ar')
        ->all();

    expect($subjectNames[0] ?? null)->toBe('التربية الاسلامية')
        ->and($subjectNames)->not->toContain('القران الكريم والتربية الاسلامية');
});
