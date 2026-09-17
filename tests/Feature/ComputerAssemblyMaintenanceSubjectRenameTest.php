<?php

use App\Infrastructure\Grades\ConfigSubjectCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('uses computer maintenance instead of assembly maintenance for the computer assembly catalog', function () {
    $catalog = new ConfigSubjectCatalog;
    $subjects = $catalog->getSubjectsFor('الحاسوب وتقنية المعلومات', 'تجميع وصيانة الحاسوب');

    expect($subjects)->toContain('صيانة الحاسوب')
        ->and($subjects)->not->toContain('صيانة وتجميع الحاسوب')
        ->and($catalog->getSubjectsFor('الحاسوب وتقنية المعلومات', 'شبكات الحاسوب'))
        ->toContain('صيانة وتجميع الحاسوب');
});

it('stores computer maintenance for the computer assembly major in the database', function () {
    if (
        ! Schema::hasTable('branches')
        || ! Schema::hasTable('majors')
        || ! Schema::hasTable('major_subjects')
        || ! Schema::hasTable('subjects')
    ) {
        $this->markTestSkipped('Normalized majors schema is not available.');
    }

    $branchId = DB::table('branches')->where('name_ar', 'الحاسوب وتقنية المعلومات')->value('id');
    expect($branchId)->not->toBeNull();

    $major = DB::table('majors')
        ->where('branch_id', $branchId)
        ->where('name_ar', 'تجميع وصيانة الحاسوب')
        ->first();

    expect($major)->not->toBeNull();

    $subjectNames = DB::table('major_subjects as ms')
        ->join('subjects as s', 's.id', '=', 'ms.subject_id')
        ->where('ms.major_id', $major->id)
        ->orderBy('ms.sort_order')
        ->pluck('s.name_ar')
        ->all();

    expect($subjectNames)->toContain('صيانة الحاسوب')
        ->and($subjectNames)->not->toContain('صيانة وتجميع الحاسوب');
});
