<?php

use App\Infrastructure\Grades\ConfigSubjectCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('uses production and operations instead of operations and production for the management catalog', function () {
    $catalog = new ConfigSubjectCatalog;
    $subjects = $catalog->getSubjectsFor('التجاري', 'ادارة');

    expect($subjects)->toContain('ادارة الانتاج والعمليات')
        ->and($subjects)->not->toContain('ادارة العمليات والانتاج')
        ->and($catalog->getSubjectsFor('التجاري', 'محاسبة'))
        ->not->toContain('ادارة الانتاج والعمليات');
});

it('stores production and operations for the management major in the database', function () {
    if (
        ! Schema::hasTable('branches')
        || ! Schema::hasTable('majors')
        || ! Schema::hasTable('major_subjects')
        || ! Schema::hasTable('subjects')
    ) {
        $this->markTestSkipped('Normalized majors schema is not available.');
    }

    $branchId = DB::table('branches')->where('name_ar', 'التجاري')->value('id');
    expect($branchId)->not->toBeNull();

    $major = DB::table('majors')
        ->where('branch_id', $branchId)
        ->where('name_ar', 'ادارة')
        ->first();

    expect($major)->not->toBeNull();

    $subjectNames = DB::table('major_subjects as ms')
        ->join('subjects as s', 's.id', '=', 'ms.subject_id')
        ->where('ms.major_id', $major->id)
        ->orderBy('ms.sort_order')
        ->pluck('s.name_ar')
        ->all();

    expect($subjectNames)->toContain('ادارة الانتاج والعمليات')
        ->and($subjectNames)->not->toContain('ادارة العمليات والانتاج');
});
