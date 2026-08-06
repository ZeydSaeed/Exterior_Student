<?php

use App\Application\Student\Query\ListRepeatersReportQuery;
use App\Application\Student\Query\ListRepeatersReportQueryHandler;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

it('lists all major subjects and counts failing repeaters per subject', function () {
    $repo = Mockery::mock(StudentQueryRepository::class);
    $catalog = Mockery::mock(SubjectCatalogInterface::class);

    $repo->shouldReceive('listRepeatersReport')->once()->andReturn([
        'groups' => [[
            'branch' => 'الصناعي',
            'major' => 'كهرباء',
            'count' => 2,
            'students' => [
                [
                    'id' => 1,
                    'exam_number' => '100',
                    'full_name' => 'طالب واحد',
                    'subjects' => [
                        ['subject' => 'اللغة العربية', 'score' => '40'],
                        ['subject' => 'الرياضيات', 'score' => '70'],
                        ['subject' => 'الطبيعيات', 'score' => '30'],
                    ],
                    'total' => '140',
                    'average' => '46',
                    'result' => 'معيد',
                ],
                [
                    'id' => 2,
                    'exam_number' => '101',
                    'full_name' => 'طالب اثنان',
                    'subjects' => [
                        ['subject' => 'اللغة العربية', 'score' => '35'],
                        ['subject' => 'الرياضيات', 'score' => '20'],
                        ['subject' => 'الطبيعيات', 'score' => '80'],
                    ],
                    'total' => '135',
                    'average' => '45',
                    'result' => 'معيد',
                ],
            ],
        ]],
        'stats' => ['total_repeaters' => 2],
        'filters' => [
            'academicYears' => collect(['2025-2026']),
            'branches' => collect(['الصناعي']),
            'majors' => collect(['كهرباء']),
            'genders' => collect(['ذكر']),
        ],
    ]);

    $catalog->shouldReceive('getSubjectsFor')->once()->with('الصناعي', 'كهرباء')->andReturn([
        'اللغة العربية',
        'التربية الاسلامية',
        'اللغة الانكليزية',
        'الرياضيات',
        'الطبيعيات',
        'الرسم الصناعي',
        'العلوم الصناعية',
        'التدريب العملي',
    ]);

    $handler = new ListRepeatersReportQueryHandler($repo, $catalog);
    $result = $handler->handle(ListRepeatersReportQuery::fromArray([
        'branch' => 'الصناعي',
        'major' => 'كهرباء',
        'year' => '2025-2026',
    ]));

    $group = $result->groups[0];

    expect($group['subject_columns'])->toBe([
        'اللغة العربية',
        'الرياضيات',
        'الطبيعيات',
    ])
        ->and($group['subject_repeater_counts'])->toBe([
            'اللغة العربية' => 2,
            'الرياضيات' => 1,
            'الطبيعيات' => 1,
        ])
        ->and($group['subject_columns'])->not->toContain('التربية الاسلامية')
        ->and($group['subject_columns'])->not->toContain('التدريب العملي');
});
