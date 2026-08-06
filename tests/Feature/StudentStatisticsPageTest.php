<?php

use App\Application\Student\DTO\ListStudentStatisticsResponseDTO;
use App\Application\Student\Query\ListStudentStatisticsQuery;
use App\Application\Student\Query\ListStudentStatisticsQueryHandler;
use App\Domain\Student\StudentQueryRepository;

it('builds statistics from filtered student count and selected labels', function () {
    $repo = Mockery::mock(StudentQueryRepository::class);
    $repo->shouldReceive('countWithFilters')
        ->once()
        ->with([
            'branch' => 'الصناعي',
            'major' => 'كهرباء',
            'gender' => null,
            'year' => '2025-2026',
            'round' => null,
            'result' => null,
            'search' => null,
        ])
        ->andReturn(42);
    $repo->shouldReceive('getFilterLists')->once()->andReturn([
        'academicYears' => collect(['2025-2026']),
        'branches' => collect(['الصناعي']),
        'majors' => collect(['كهرباء']),
        'genders' => collect(['ذكر', 'أنثى']),
        'resultOptions' => collect(['ناجح']),
        'roundOptions' => collect(['الاول']),
    ]);

    $handler = new ListStudentStatisticsQueryHandler($repo);
    $result = $handler->handle(ListStudentStatisticsQuery::fromArray([
        'branch' => 'الصناعي',
        'major' => 'كهرباء',
        'year' => '2025-2026',
    ]));

    expect($result)->toBeInstanceOf(ListStudentStatisticsResponseDTO::class)
        ->and($result->totalStudents)->toBe(42)
        ->and($result->selectedFilters['branch'])->toBe('الصناعي')
        ->and($result->selectedFilters['major'])->toBe('كهرباء')
        ->and($result->selectedFilters['year'])->toBe('2025-2026')
        ->and($result->selectedFilters['gender'])->toBe('');
});

it('registers the statistics route and sidebar label', function () {
    expect(route('students.statistics.index'))->toContain('/students/statistics');

    $sidebar = file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    expect($sidebar)->toContain('الاحصائيات')
        ->and($sidebar)->toContain('students.statistics.index');
});
