<?php

use App\Application\Profile\Command\CreateStudentNoteCommandHandler;
use App\Application\Profile\Command\DeleteStudentNoteCommandHandler;
use App\Application\Profile\Command\UpdateStudentNoteCommandHandler;
use App\Domain\StudentNote\StudentNote;
use App\Domain\StudentNote\StudentNoteCommandRepository;

it('maps a student note from a row', function () {
    $note = StudentNote::fromRow((object) [
        'id' => 3,
        'student_id' => 9,
        'body' => 'ملاحظة الطالب',
    ]);

    expect($note->id)->toBe(3)
        ->and($note->studentId)->toBe(9)
        ->and($note->body)->toBe('ملاحظة الطالب');
});

it('passes a trimmed note body to the repository when creating', function () {
    $repository = Mockery::mock(StudentNoteCommandRepository::class);
    $repository->shouldReceive('create')
        ->once()
        ->with(1, 'ملاحظة الحفظ');

    (new CreateStudentNoteCommandHandler($repository))->handle(1, '  ملاحظة الحفظ  ');
});

it('passes a trimmed note body to the repository when updating', function () {
    $repository = Mockery::mock(StudentNoteCommandRepository::class);
    $repository->shouldReceive('update')
        ->once()
        ->with(1, 4, 'ملاحظة محدثة');

    (new UpdateStudentNoteCommandHandler($repository))->handle(1, 4, ' ملاحظة محدثة ');
});

it('deletes a student note by student and note id', function () {
    $repository = Mockery::mock(StudentNoteCommandRepository::class);
    $repository->shouldReceive('delete')
        ->once()
        ->with(1, 4);

    (new DeleteStudentNoteCommandHandler($repository))->handle(1, 4);
});
