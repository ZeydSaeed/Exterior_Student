<?php

use App\Support\StudentBranchMajors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('includes فن الديكور under فنون تطبيقية in config', function () {
    $majors = StudentBranchMajors::majorsForBranch('فنون تطبيقية');

    expect($majors)->toContain('فن الديكور')
        ->and(config('grades_catalog.catalog.فنون تطبيقية.فن الديكور'))->toBe('subjects_decor');
});

it('stores فن الديكور major under فنون تطبيقية in the database', function () {
    if (! Schema::hasTable('branches') || ! Schema::hasTable('majors')) {
        $this->markTestSkipped('Normalized majors schema is not available.');
    }

    $branchId = DB::table('branches')->where('name_ar', 'فنون تطبيقية')->value('id');
    expect($branchId)->not->toBeNull();

    $major = DB::table('majors')
        ->where('branch_id', $branchId)
        ->where('name_ar', 'فن الديكور')
        ->first();

    expect($major)->not->toBeNull();

    if (Schema::hasTable('major_subjects') && Schema::hasTable('subjects')) {
        $subjectNames = DB::table('major_subjects as ms')
            ->join('subjects as s', 's.id', '=', 'ms.subject_id')
            ->where('ms.major_id', $major->id)
            ->pluck('s.name_ar')
            ->all();

        expect($subjectNames)->toContain('تقنيات الديكور')
            ->and($subjectNames)->toContain('مكملات الديكور');
    }
});
