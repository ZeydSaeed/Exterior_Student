<?php

use App\Domain\Student\StudentListProjection;
use App\Domain\Student\StudentQueryRepository;
use Illuminate\Pagination\LengthAwarePaginator;

it('filters the students list through a layout-free fragment', function () {
    $repository = Mockery::mock(StudentQueryRepository::class);
    $repository->shouldReceive('listWithFilters')->once()->andReturn(new StudentListProjection(
        students: new LengthAwarePaginator([], 0, 20),
        academicYears: collect(),
        branches: collect(),
        majors: collect(),
        genders: collect(),
        resultOptions: collect(),
        roundOptions: collect(),
    ));
    $this->app->instance(StudentQueryRepository::class, $repository);

    $this->get(route('students.index'), [
        'X-Students-List-Fragment' => '1',
    ])
        ->assertSuccessful()
        ->assertSee('data-students-fragment="table"', false)
        ->assertSee('data-students-fragment="summary"', false)
        ->assertSee('students-table', false)
        ->assertSee('dashboard-toolbar-filters-summary', false)
        ->assertDontSee('dashboard-sidebar', false)
        ->assertDontSee('js/students/index.js', false)
        ->assertDontSee('fonts.bunny.net', false);
});

it('keeps the full students page available without the fragment header', function () {
    $source = (string) file_get_contents(app_path('Http/Controllers/StudentController.php'));
    $index = (string) file_get_contents(resource_path('views/students/index.blade.php'));

    expect($source)->toContain("headers->get('X-Students-List-Fragment')")
        ->and($source)->toContain('students.partials.list-fragment')
        ->and($source)->toContain('students.index')
        ->and($index)->toContain('js/students/list-fast-filter.js')
        ->and($index)->toContain('js/students/index.js');
});

it('applies sidebar filters without a full page reload on the students list', function () {
    $filters = (string) file_get_contents(resource_path('views/students/partials/filters.blade.php'));
    $script = (string) file_get_contents(public_path('js/students/list-fast-filter.js'));
    $js = (string) file_get_contents(public_path('js/students/index.js'));
    $css = (string) file_get_contents(public_path('css/dashboard.css'));

    expect($filters)->toContain('StudentsListFastFilter')
        ->and($filters)->toContain('window.location = url')
        ->and($script)->toContain("'X-Students-List-Fragment': '1'")
        ->and($script)->toContain('history.pushState')
        ->and($script)->toContain('page-repeaters')
        ->and($js)->toContain("e.target.closest('.btn-grades-open')")
        ->and($js)->toContain("e.target.closest('.btn-edit-row')")
        ->and($js)->not->toContain('openBtns.forEach')
        ->and($css)->toContain("--dashboard-filter-font: 'Cairo', Tahoma, sans-serif")
        ->and($css)->toContain('width: 1.75cm');
});

it('filters normalized student rows by foreign keys instead of lookup names', function () {
    $source = (string) file_get_contents(app_path('Infrastructure/Persistence/MySQLStudentQueryRepository.php'));

    expect($source)->toContain("where('a.branch_id'")
        ->and($source)->toContain("where('a.major_id'")
        ->and($source)->toContain("where('a.academic_year_id'")
        ->and($source)->toContain("whereIn('a.result_type_id'")
        ->and($source)->toContain('function catalogId')
        ->and($source)->toContain('StudentListQueryCache::rememberPage')
        ->and($source)->not->toContain("where('b.name_ar', \$filters['branch'])")
        ->and($source)->not->toContain("where('m.name_ar', \$filters['major'])")
        ->and($source)->not->toContain("where('y.year_label', \$filters['year'])");
});
