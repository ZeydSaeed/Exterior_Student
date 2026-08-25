<?php

use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;

it('shows the enrollment number column before the exam number in the students table', function () {
    $html = file_get_contents(resource_path('views/students/partials/table.blade.php'));

    expect($html)->toContain('students-enrollment-cell')
        ->and($html)->not->toContain('students-action-badge-enroll')
        ->and(strpos($html, '>رقم القيد</th>'))->toBeLessThan(strpos($html, '>الرقم الامتحاني</th>'));
});

it('shows an editable enrollment number field before the exam number in the grades modal', function () {
    $html = file_get_contents(resource_path('views/students/partials/grades-modal.blade.php'));

    expect($html)->toContain('name="enrollment_number"')
        ->and(strpos($html, 'grades-enrollment-number-input'))->toBeLessThan(strpos($html, 'grades-exam-number-input'));
});

it('saves the enrollment number when student data is updated from the grades modal', function () {
    $commandRepo = Mockery::mock(StudentCommandRepository::class);
    $commandRepo->shouldReceive('updateGrades')
        ->once()
        ->with(1, Mockery::on(function (array $payload): bool {
            return ($payload['enrollment_number'] ?? null) === '8821'
                && ($payload['exam_number'] ?? null) === '12345';
        }))
        ->andReturn(true);
    $this->app->instance(StudentCommandRepository::class, $commandRepo);

    $queryRepo = Mockery::mock(StudentQueryRepository::class);
    $queryRepo->shouldReceive('getStudentDocumentInfo')->andReturn(null);
    $this->app->instance(StudentQueryRepository::class, $queryRepo);

    $this->putJson(route('students.grades.update', ['id' => 1]), [
        'exam_number' => '12345',
        'enrollment_number' => '8821',
        'name_student' => 'أحمد',
    ])->assertSuccessful();
});

it('normalizes arabic digits in the enrollment number when updating student data', function () {
    $commandRepo = Mockery::mock(StudentCommandRepository::class);
    $commandRepo->shouldReceive('updateGrades')
        ->once()
        ->with(1, Mockery::on(fn (array $payload): bool => ($payload['enrollment_number'] ?? null) === '8821'))
        ->andReturn(true);
    $this->app->instance(StudentCommandRepository::class, $commandRepo);

    $queryRepo = Mockery::mock(StudentQueryRepository::class);
    $queryRepo->shouldReceive('getStudentDocumentInfo')->andReturn(null);
    $this->app->instance(StudentQueryRepository::class, $queryRepo);

    $this->putJson(route('students.grades.update', ['id' => 1]), [
        'exam_number' => '12345',
        'enrollment_number' => '٨٨٢١',
    ])->assertSuccessful();
});

it('rejects a non numeric enrollment number when updating student data', function () {
    $commandRepo = Mockery::mock(StudentCommandRepository::class);
    $commandRepo->shouldNotReceive('updateGrades');
    $this->app->instance(StudentCommandRepository::class, $commandRepo);

    $this->putJson(route('students.grades.update', ['id' => 1]), [
        'exam_number' => '12345',
        'enrollment_number' => 'ABC',
    ])->assertUnprocessable();
});
