<?php

use App\Domain\Attestation\AttestationQueryRepository;
use App\Domain\Record\RecordQueryRepository;
use App\Domain\Student\Student;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\StudentNote\StudentNoteCommandRepository;
use App\Domain\StudentNote\StudentNoteQueryRepository;

it('shows a student notes section below documents on the personal record page', function () {
    $view = file_get_contents(resource_path('views/students/profile.blade.php'));
    $documentsPos = strpos($view, 'employees-card-left');
    $notesPos = strpos($view, 'employees-card-notes');

    expect($view)->toContain('employees-card-notes')
        ->and($view)->toContain('students.profile.notes.store')
        ->and($view)->toContain('students.profile.notes.update')
        ->and($view)->toContain('students.profile.notes.destroy')
        ->and($view)->toContain('name="body"')
        ->and($view)->toContain('إضافة ملاحظة')
        ->and($notesPos)->toBeGreaterThan($documentsPos);
});

it('includes student notes in the personal record button badge on the students table', function () {
    $html = file_get_contents(resource_path('views/students/partials/table.blade.php'));

    expect($html)->toContain('btn-profile')
        ->and($html)->toContain('مجموع التأييدات والوثائق والملاحظات');
});

it('registers student notes routes on the personal record', function () {
    expect(route('students.profile.notes.store', ['id' => 3]))
        ->toContain('/students/3/profile/notes')
        ->and(route('students.profile.notes.update', [3, 9]))
        ->toContain('/students/3/profile/notes/9')
        ->and(route('students.profile.notes.destroy', [3, 9]))
        ->toContain('/students/3/profile/notes/9');
});

it('saves a new student note for the student and returns to the personal record', function () {
    bindStudentProfileNoteRepositories();

    $command = Mockery::mock(StudentNoteCommandRepository::class);
    $command->shouldReceive('create')->once()->with(1, 'ملاحظة جديدة');
    app()->instance(StudentNoteCommandRepository::class, $command);

    $this->from(route('students.profile.show', ['id' => 1]))
        ->post(route('students.profile.notes.store', ['id' => 1]), [
            'body' => 'ملاحظة جديدة',
        ])
        ->assertRedirect(route('students.profile.show', ['id' => 1]));
});

it('updates a student note and returns to the personal record', function () {
    bindStudentProfileNoteRepositories();

    $command = Mockery::mock(StudentNoteCommandRepository::class);
    $command->shouldReceive('update')->once()->with(1, 8, 'ملاحظة محدثة');
    app()->instance(StudentNoteCommandRepository::class, $command);

    $this->from(route('students.profile.show', ['id' => 1]))
        ->put(route('students.profile.notes.update', ['id' => 1, 'noteId' => 8]), [
            'body' => 'ملاحظة محدثة',
        ])
        ->assertRedirect(route('students.profile.show', ['id' => 1]));
});

it('deletes a student note and returns to the personal record', function () {
    bindStudentProfileNoteRepositories();

    $command = Mockery::mock(StudentNoteCommandRepository::class);
    $command->shouldReceive('delete')->once()->with(1, 8);
    app()->instance(StudentNoteCommandRepository::class, $command);

    $this->from(route('students.profile.show', ['id' => 1]))
        ->delete(route('students.profile.notes.destroy', ['id' => 1, 'noteId' => 8]))
        ->assertRedirect(route('students.profile.show', ['id' => 1]));
});

function bindStudentProfileNoteRepositories(): void
{
    $student = new Student(
        id: 1,
        exam_number: '12345',
        full_name: 'أحمد محمد',
        academic_year: null,
        result: null,
        branch: null,
        major: null,
        gender: null,
    );

    $studentRepo = Mockery::mock(StudentQueryRepository::class);
    $studentRepo->shouldReceive('getStudentById')->andReturn($student);
    app()->instance(StudentQueryRepository::class, $studentRepo);

    $attestationRepo = Mockery::mock(AttestationQueryRepository::class);
    $attestationRepo->shouldReceive('listByStudentId')->andReturn([]);
    app()->instance(AttestationQueryRepository::class, $attestationRepo);

    $recordRepo = Mockery::mock(RecordQueryRepository::class);
    $recordRepo->shouldReceive('listByStudentId')->andReturn([]);
    app()->instance(RecordQueryRepository::class, $recordRepo);

    $noteQuery = Mockery::mock(StudentNoteQueryRepository::class);
    $noteQuery->shouldReceive('listByStudentId')->andReturn([]);
    app()->instance(StudentNoteQueryRepository::class, $noteQuery);
}
