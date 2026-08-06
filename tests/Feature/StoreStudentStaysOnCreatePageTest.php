<?php

use App\Domain\Student\StudentCommandRepository;

it('redirects back to the create student page after a successful save', function () {
    $repo = Mockery::mock(StudentCommandRepository::class);
    $repo->shouldReceive('create')->once()->andReturn(1);
    $this->app->instance(StudentCommandRepository::class, $repo);

    $response = $this->from(route('students.create'))
        ->post(route('students.store'), [
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
        ]);

    $response->assertRedirect(route('students.create'))
        ->assertSessionMissing('status');
});
