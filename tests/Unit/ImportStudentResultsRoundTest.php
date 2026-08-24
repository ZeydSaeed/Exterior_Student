<?php

use App\Application\Student\Import\ImportStudentResultsFromExcelUseCase;
use App\Application\Student\Service\AllBlockedGradesResultResolver;
use App\Application\Student\Service\CompletedSubjectsCalculator;
use App\Application\Student\Service\FirstRoundSubjectsLocker;
use App\Application\Student\Service\GradesTotalCalculator;
use App\Domain\Student\BranchMajorCatalogInterface;
use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\StudentResultsImportTempRepository;
use App\Domain\Student\SubjectCatalogInterface;

it('maps the selected round into each staged results-import row', function () {
    $query = Mockery::mock(StudentQueryRepository::class);
    $command = Mockery::mock(StudentCommandRepository::class);
    $subjects = Mockery::mock(SubjectCatalogInterface::class);

    $useCase = new ImportStudentResultsFromExcelUseCase(
        Mockery::mock(StudentResultsImportTempRepository::class),
        $query,
        $command,
        Mockery::mock(BranchMajorCatalogInterface::class),
        $subjects,
        new FirstRoundSubjectsLocker($query, $command, $subjects, new CompletedSubjectsCalculator),
        new GradesTotalCalculator,
        new AllBlockedGradesResultResolver($subjects),
    );

    $method = new ReflectionMethod(ImportStudentResultsFromExcelUseCase::class, 'mapRow');
    $method->setAccessible(true);

    $mapped = $method->invoke(
        $useCase,
        ['12345', 'اسم', 'الصناعي', 'سيارات', '2024-2025', '80', '70', '60', '50', '90', '85', '75', '65', '575', '71.8', 'ناجح'],
        ['الرقم', 'الاسم', 'الفرع', 'الاختصاص', 'العام', 'م1', 'م2', 'م3', 'م4', 'م5', 'م6', 'م7', 'م8', 'المجموع', 'المعدل', 'النتيجة'],
        1,
        'الاول تكميلي',
    );

    expect($mapped)->not->toBeNull()
        ->and($mapped['exam_number'])->toBe('12345')
        ->and($mapped['round'])->toBe('الاول تكميلي');
});

it('includes the staged round when processing valid results import rows', function () {
    $temp = Mockery::mock(StudentResultsImportTempRepository::class);
    $query = Mockery::mock(StudentQueryRepository::class);
    $command = Mockery::mock(StudentCommandRepository::class);
    $branchMajor = Mockery::mock(BranchMajorCatalogInterface::class);
    $subjects = Mockery::mock(SubjectCatalogInterface::class);
    $locker = new FirstRoundSubjectsLocker($query, $command, $subjects, new CompletedSubjectsCalculator);

    $useCase = new ImportStudentResultsFromExcelUseCase(
        $temp,
        $query,
        $command,
        $branchMajor,
        $subjects,
        $locker,
        new GradesTotalCalculator,
        new AllBlockedGradesResultResolver($subjects),
    );

    $temp->shouldReceive('getByBatchId')->once()->with('batch-1')->andReturn([
        (object) [
            'id' => 1,
            'row_index' => 1,
            'status' => 'valid',
            'student_id' => 10,
            'branch' => 'الصناعي',
            'major' => 'سيارات',
            'academic_year' => '2024-2025',
            'average' => '70',
            'result' => 'ناجح',
            'round' => 'الثاني',
            'subjects_json' => json_encode([
                ['idx' => 5, 'subject' => 'م1', 'score' => '80'],
                ['idx' => 6, 'subject' => 'م2', 'score' => '70'],
                ['idx' => 7, 'subject' => 'م3', 'score' => '60'],
                ['idx' => 8, 'subject' => 'م4', 'score' => '50'],
                ['idx' => 9, 'subject' => 'م5', 'score' => '90'],
                ['idx' => 10, 'subject' => 'م6', 'score' => '85'],
                ['idx' => 11, 'subject' => 'م7', 'score' => '75'],
                ['idx' => 12, 'subject' => 'م8', 'score' => '65'],
            ], JSON_UNESCAPED_UNICODE),
        ],
    ]);
    $subjects->shouldReceive('getSubjectsFor')->with('الصناعي', 'سيارات')->andReturn([
        'اللغة العربية',
        'التربية الاسلامية',
        'اللغة الانكليزية',
        'الرياضيات',
        'الطبيعيات',
        'الرسم الصناعي',
        'العلوم الصناعية',
        'التدريب العملي',
    ]);
    $command->shouldReceive('updateGrades')
        ->once()
        ->withArgs(function (int $id, array $payload): bool {
            return $id === 10 && ($payload['round'] ?? null) === 'الثاني';
        })
        ->andReturn(true);
    $query->shouldReceive('getStudentDocumentInfo')->once()->with(10)->andReturn(null);
    $temp->shouldReceive('deleteByBatchId')->once()->with('batch-1');

    $result = $useCase->processValidRows('batch-1');

    expect($result['success'])->toBe(1)
        ->and($result['failed'])->toBe(0);
});
