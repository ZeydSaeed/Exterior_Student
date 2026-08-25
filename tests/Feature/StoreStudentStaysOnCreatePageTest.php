<?php

use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;

/**
 * @return array<string, string>
 */
function validStoreStudentPayload(array $overrides = []): array
{
    return array_merge([
        'exam_number' => '12345',
        'name_student' => 'أحمد',
        'name_father' => 'محمد',
        'name_grandfather' => 'علي',
        'name_surname' => 'حسين',
        'birth_date' => '2006-06-15',
        'birth_place' => 'بغداد',
        'mother_full_name' => 'فاطمة',
        'gender' => 'ذكر',
        'branch' => 'الصناعي',
        'major' => 'سيارات',
        'academic_year' => '2024-2025',
    ], $overrides);
}

it('shows the optional enrollment number field before the exam number on the create page', function () {
    $queryRepo = Mockery::mock(StudentQueryRepository::class);
    $queryRepo->shouldReceive('getAcademicYearsForForm')->once()->andReturn(['2024-2025']);
    $this->app->instance(StudentQueryRepository::class, $queryRepo);

    $this->get(route('students.create'))
        ->assertSuccessful()
        ->assertSee('name="enrollment_number"', false)
        ->assertSeeInOrder(['رقم القيد', 'الرقم الامتحاني']);
});

it('redirects back to the create student page after a successful save', function () {
    $repo = Mockery::mock(StudentCommandRepository::class);
    $repo->shouldReceive('create')->once()->andReturn(1);
    $this->app->instance(StudentCommandRepository::class, $repo);

    $response = $this->from(route('students.create'))
        ->post(route('students.store'), validStoreStudentPayload());

    $response->assertRedirect(route('students.create'))
        ->assertSessionMissing('status');
});

it('saves the optional enrollment number with the student', function () {
    $repo = Mockery::mock(StudentCommandRepository::class);
    $repo->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function (array $data): bool {
            return ($data['enrollment_number'] ?? null) === '8821'
                && ($data['exam_number'] ?? null) === '12345';
        }))
        ->andReturn(1);
    $this->app->instance(StudentCommandRepository::class, $repo);

    $this->from(route('students.create'))
        ->post(route('students.store'), validStoreStudentPayload([
            'enrollment_number' => '8821',
        ]))
        ->assertRedirect(route('students.create'));
});

it('normalizes arabic digits in the enrollment number before save', function () {
    $repo = Mockery::mock(StudentCommandRepository::class);
    $repo->shouldReceive('create')
        ->once()
        ->with(Mockery::on(fn (array $data): bool => ($data['enrollment_number'] ?? null) === '8821'))
        ->andReturn(1);
    $this->app->instance(StudentCommandRepository::class, $repo);

    $this->from(route('students.create'))
        ->post(route('students.store'), validStoreStudentPayload([
            'enrollment_number' => '٨٨٢١',
        ]))
        ->assertRedirect(route('students.create'));
});

it('allows saving a student without an enrollment number', function () {
    $repo = Mockery::mock(StudentCommandRepository::class);
    $repo->shouldReceive('create')
        ->once()
        ->with(Mockery::on(fn (array $data): bool => array_key_exists('enrollment_number', $data) && $data['enrollment_number'] === null))
        ->andReturn(1);
    $this->app->instance(StudentCommandRepository::class, $repo);

    $this->from(route('students.create'))
        ->post(route('students.store'), validStoreStudentPayload())
        ->assertRedirect(route('students.create'));
});

it('rejects a non numeric enrollment number', function () {
    $repo = Mockery::mock(StudentCommandRepository::class);
    $repo->shouldNotReceive('create');
    $this->app->instance(StudentCommandRepository::class, $repo);

    $this->from(route('students.create'))
        ->post(route('students.store'), validStoreStudentPayload([
            'enrollment_number' => 'ABC',
        ]))
        ->assertRedirect(route('students.create'))
        ->assertSessionHas('app_dialog');
});
