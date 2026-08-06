<?php

use App\Application\Student\Query\GetBulkStudentDocumentsPrintQueryHandler;
use App\Application\Student\Query\ListStudentsQuery;
use App\Domain\Student\StudentQueryRepository;

it('returns male and female counts for the same filtered documents set', function () {
    $repo = Mockery::mock(StudentQueryRepository::class);
    $repo->shouldReceive('countGendersWithFilters')
        ->once()
        ->with([
            'branch' => 'الصناعي',
            'major' => null,
            'gender' => null,
            'year' => '2025-2026',
            'round' => null,
            'result' => null,
            'search' => null,
        ])
        ->andReturn(['male' => 60, 'female' => 40]);

    $reflection = new ReflectionClass(GetBulkStudentDocumentsPrintQueryHandler::class);
    $handler = $reflection->newInstanceWithoutConstructor();
    $property = $reflection->getProperty('studentRepository');
    $property->setAccessible(true);
    $property->setValue($handler, $repo);

    $counts = $handler->genderCounts(ListStudentsQuery::fromArray([
        'branch' => 'الصناعي',
        'year' => '2025-2026',
    ]));

    expect($counts)->toBe(['male' => 60, 'female' => 40])
        ->and($counts['male'] + $counts['female'])->toBe(100);
});

it('shows gender counts labels on the documents bulk print page', function () {
    $html = file_get_contents(dirname(__DIR__, 2).'/resources/views/students/documents-bulk-print.blade.php');

    expect($html)->toContain('الذكور:')
        ->and($html)->toContain('الإناث:')
        ->and($html)->toContain('عدد القيود:');
});
