<?php

use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

it('uses the same select filter layout as the students list on the repeaters page', function () {
    $students = (string) file_get_contents(resource_path('views/students/partials/filters.blade.php'));
    $repeaters = (string) file_get_contents(resource_path('views/students/repeaters/index.blade.php'));

    $branchPos = strpos($students, 'for="students-filter-branch"');
    $majorPos = strpos($students, 'for="students-filter-major"');
    $genderPos = strpos($students, 'for="students-filter-gender"');
    $yearPos = strpos($students, 'for="students-filter-year"');
    $roundPos = strpos($students, 'for="students-filter-round"');
    $resultPos = strpos($students, 'for="students-filter-result"');

    expect($repeaters)->toContain('students.partials.filters')
        ->and($repeaters)->toContain('students.repeaters.index')
        ->and($repeaters)->not->toContain('repeaters-filter-radio')
        ->and($students)->toContain('students-filter-select')
        ->and($students)->toContain('students-filter-clear-all')
        ->and($branchPos)->not->toBeFalse()
        ->and($majorPos)->toBeGreaterThan($branchPos)
        ->and($genderPos)->toBeGreaterThan($majorPos)
        ->and($yearPos)->toBeGreaterThan($genderPos)
        ->and($roundPos)->toBeGreaterThan($yearPos)
        ->and($resultPos)->toBeGreaterThan($roundPos);
});

it('renders student-style select filters on the repeaters page', function () {
    $repository = Mockery::mock(StudentQueryRepository::class);
    $repository->shouldReceive('listRepeatersReport')->once()->andReturn([
        'groups' => [],
        'stats' => ['total_repeaters' => 0],
        'filters' => [
            'academicYears' => collect(['2025-2026']),
            'branches' => collect(['الصناعي']),
            'majors' => collect(['كهرباء']),
            'genders' => collect(['ذكر']),
            'roundOptions' => collect(['الاول']),
        ],
    ]);
    $this->app->instance(StudentQueryRepository::class, $repository);
    $this->app->instance(SubjectCatalogInterface::class, Mockery::mock(SubjectCatalogInterface::class));

    $this->get(route('students.repeaters.index'))
        ->assertSuccessful()
        ->assertSee('students-filter-sidebar', false)
        ->assertSee('students-filter-select', false)
        ->assertSee('id="students-filter-branch"', false)
        ->assertSee('id="students-filter-major"', false)
        ->assertSee('id="students-filter-gender"', false)
        ->assertSee('id="students-filter-year"', false)
        ->assertSee('id="students-filter-round"', false)
        ->assertSee('id="students-filter-result"', false)
        ->assertSee('إلغاء الكل')
        ->assertDontSee('repeaters-filter-radio', false)
        ->assertDontSee('العام الدراسي (مطلوب)');
});
