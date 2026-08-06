<?php

use App\Application\Student\Import\ImportStudentResultsFromExcelUseCase;
use App\Application\Student\Service\CompletedSubjectsCalculator;
use App\Application\Student\Service\FirstRoundSubjectsLocker;
use App\Application\Student\Service\GradesTotalCalculator;
use App\Domain\Student\BranchMajorCatalogInterface;
use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\StudentResultsImportTempRepository;
use App\Domain\Student\SubjectCatalogInterface;
use App\Infrastructure\Persistence\MySQLStudentCommandRepository;

it('preserves numeric and textual subject scores for storage', function (string $input, string $expected, bool $clamp) {
    $repo = new MySQLStudentCommandRepository;
    $method = new ReflectionMethod(MySQLStudentCommandRepository::class, 'normalizeScoreForStorage');
    $method->setAccessible(true);

    expect($method->invoke($repo, $input, $clamp))->toBe($expected);
})->with([
    'numeric plain' => ['85', '85', false],
    'numeric rounded' => ['70.6', '71', false],
    'numeric clamped high' => ['150', '100', true],
    'numeric clamped low' => ['-5', '0', true],
    'absent mark' => ['غ', 'غ', true],
    'blocked mark' => ['حجب', 'حجب', false],
    'empty becomes zero' => ['', '0', false],
]);

it('does not reject textual subject scores during results import validation', function () {
    $temp = Mockery::mock(StudentResultsImportTempRepository::class);
    $query = Mockery::mock(StudentQueryRepository::class);
    $command = Mockery::mock(StudentCommandRepository::class);
    $branchMajor = Mockery::mock(BranchMajorCatalogInterface::class);
    $subjects = Mockery::mock(SubjectCatalogInterface::class);
    $locker = new FirstRoundSubjectsLocker(
        $query,
        $command,
        $subjects,
        new CompletedSubjectsCalculator,
    );
    $totals = new GradesTotalCalculator;

    $useCase = new ImportStudentResultsFromExcelUseCase(
        $temp,
        $query,
        $command,
        $branchMajor,
        $subjects,
        $locker,
        $totals,
    );

    $student = (object) [
        'id' => 10,
        'full_name' => 'أحمد محمد علي حسين',
        'branch' => 'الصناعي',
        'major' => 'سيارات',
        'academic_year' => '2024-2025',
    ];

    $query->shouldReceive('findByExamNumber')->once()->with('12345')->andReturn($student);
    $branchMajor->shouldReceive('majorBelongsToBranch')->once()->with('سيارات', 'الصناعي')->andReturn(true);
    $subjects->shouldReceive('getSubjectsFor')->once()->with('الصناعي', 'سيارات')->andReturn([
        'اللغة العربية',
        'التربية الاسلامية',
        'اللغة الانكليزية',
        'الرياضيات',
        'الطبيعيات',
        'الرسم الصناعي',
        'العلوم الصناعية',
        'التدريب العملي',
    ]);

    $row = (object) [
        'id' => 1,
        'exam_number' => '12345',
        'student_name' => 'أحمد محمد علي حسين',
        'branch' => 'الصناعي',
        'major' => 'سيارات',
        'academic_year' => '2024-2025',
        'subjects_json' => json_encode([
            ['idx' => 5, 'subject' => 'ع1', 'score' => 'غ'],
            ['idx' => 6, 'subject' => 'ع2', 'score' => 'حجب'],
            ['idx' => 7, 'subject' => 'ع3', 'score' => '80'],
            ['idx' => 8, 'subject' => 'ع4', 'score' => '70'],
            ['idx' => 9, 'subject' => 'ع5', 'score' => '65'],
            ['idx' => 10, 'subject' => 'ع6', 'score' => '90'],
            ['idx' => 11, 'subject' => 'ع7', 'score' => '55'],
            ['idx' => 12, 'subject' => 'ع8', 'score' => '60'],
        ], JSON_UNESCAPED_UNICODE),
    ];

    $method = new ReflectionMethod(ImportStudentResultsFromExcelUseCase::class, 'validateRow');
    $method->setAccessible(true);
    [$studentId, $errors] = $method->invoke($useCase, $row);

    expect($studentId)->toBe(10)
        ->and($errors)->toBe([]);
});

it('accepts results import without matching the student full name', function () {
    $temp = Mockery::mock(StudentResultsImportTempRepository::class);
    $query = Mockery::mock(StudentQueryRepository::class);
    $command = Mockery::mock(StudentCommandRepository::class);
    $branchMajor = Mockery::mock(BranchMajorCatalogInterface::class);
    $subjects = Mockery::mock(SubjectCatalogInterface::class);
    $locker = new FirstRoundSubjectsLocker(
        $query,
        $command,
        $subjects,
        new CompletedSubjectsCalculator,
    );

    $useCase = new ImportStudentResultsFromExcelUseCase(
        $temp,
        $query,
        $command,
        $branchMajor,
        $subjects,
        $locker,
        new GradesTotalCalculator,
    );

    $query->shouldReceive('findByExamNumber')->once()->with('12345')->andReturn((object) [
        'id' => 10,
        'full_name' => 'أحمد محمد علي حسين',
        'branch' => 'الصناعي',
        'major' => 'سيارات',
        'academic_year' => '2024-2025',
    ]);
    $branchMajor->shouldReceive('majorBelongsToBranch')->once()->with('سيارات', 'الصناعي')->andReturn(true);
    $subjects->shouldReceive('getSubjectsFor')->once()->with('الصناعي', 'سيارات')->andReturn([
        'اللغة العربية',
        'التربية الاسلامية',
        'اللغة الانكليزية',
        'الرياضيات',
        'الطبيعيات',
        'الرسم الصناعي',
        'العلوم الصناعية',
        'التدريب العملي',
    ]);

    $row = (object) [
        'id' => 1,
        'exam_number' => '12345',
        'student_name' => 'اسم مختلف تماما',
        'branch' => 'الصناعي',
        'major' => 'سيارات',
        'academic_year' => '2024-2025',
        'subjects_json' => json_encode([
            ['idx' => 5, 'subject' => 'ع1', 'score' => '80'],
            ['idx' => 6, 'subject' => 'ع2', 'score' => '70'],
            ['idx' => 7, 'subject' => 'ع3', 'score' => '60'],
            ['idx' => 8, 'subject' => 'ع4', 'score' => '50'],
            ['idx' => 9, 'subject' => 'ع5', 'score' => '90'],
            ['idx' => 10, 'subject' => 'ع6', 'score' => '85'],
            ['idx' => 11, 'subject' => 'ع7', 'score' => '75'],
            ['idx' => 12, 'subject' => 'ع8', 'score' => '65'],
        ], JSON_UNESCAPED_UNICODE),
    ];

    $method = new ReflectionMethod(ImportStudentResultsFromExcelUseCase::class, 'validateRow');
    $method->setAccessible(true);
    [$studentId, $errors] = $method->invoke($useCase, $row);

    expect($studentId)->toBe(10)
        ->and($errors)->toBe([]);
});
