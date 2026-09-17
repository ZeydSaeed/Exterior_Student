<?php

use App\Application\Student\Query\ListStudentsQueryHandler;
use App\Domain\Student\StudentQueryRepository;

it('loads student list profile badges without correlated count subqueries', function () {
    $source = (string) file_get_contents(app_path('Infrastructure/Persistence/MySQLStudentQueryRepository.php'));

    expect($source)->toContain('function hydrateListProfileCounts')
        ->and($source)->toContain('function applyExamNumberOrder')
        ->and($source)->not->toContain('AS attest_without_count')
        ->and($source)->not->toContain('(SELECT COUNT(*) FROM certificate c WHERE c.student_id = s.id')
        ->and($source)->not->toContain('(SELECT COUNT(*) FROM records r WHERE r.student_id = s.id)');
});

it('opens bulk print filters without paginating the student list', function () {
    $source = (string) file_get_contents(app_path('Http/Controllers/StudentDocumentsBulkPrintController.php'));

    expect($source)->toContain('filterLists()')
        ->and($source)->not->toContain('listHandler->handle(');
});

it('returns filter lists without querying the student page', function () {
    $repository = Mockery::mock(StudentQueryRepository::class);
    $repository->shouldReceive('getFilterLists')->once()->andReturn([
        'academicYears' => collect(['2025-2026']),
        'branches' => collect(['الصناعي']),
        'majors' => collect(['كهرباء']),
        'genders' => collect(['ذكر']),
        'resultOptions' => collect(['ناجح']),
        'roundOptions' => collect(['الاول']),
    ]);
    $repository->shouldReceive('listWithFilters')->never();

    $handler = new ListStudentsQueryHandler($repository);
    $lists = $handler->filterLists();

    expect($lists['branches']->all())->toBe(['الصناعي'])
        ->and($lists['majors']->all())->toBe(['كهرباء']);
});

it('keeps dashboard fonts sizes and styles unchanged while prefetching navigation', function () {
    $layout = (string) file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    $css = (string) file_get_contents(public_path('css/dashboard.css'));
    $prefetch = (string) file_get_contents(public_path('js/nav-prefetch.js'));

    expect($layout)->toContain('cairo:400,600,700,800')
        ->and($layout)->toContain('fonts.bunny.net')
        ->and($layout)->toContain('css/dashboard.css')
        ->and($layout)->toContain('js/nav-prefetch.js')
        ->and($layout)->toContain('type="speculationrules"')
        ->and($css)->toContain("--dashboard-filter-font: 'Cairo', Tahoma, sans-serif")
        ->and($css)->toContain('width: 1.75cm')
        ->and($prefetch)->toContain("link.rel = 'prefetch'")
        ->and($prefetch)->toContain('warmupStudentsLink')
        ->and($prefetch)->not->toContain('style.');
});
