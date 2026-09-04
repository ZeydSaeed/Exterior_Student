<?php

use App\Domain\Record\RecordCommandRepository;
use App\Domain\Record\RecordQueryRepository;
use App\Domain\Student\Student;
use App\Domain\Student\StudentQueryRepository;

it('updates all modified document fields and redirects back to documents page', function () {
    $student = new Student(
        id: 5,
        exam_number: '100',
        full_name: 'طالب',
        academic_year: null,
        result: null,
        branch: null,
        major: null,
        gender: null,
    );

    $studentRepo = Mockery::mock(StudentQueryRepository::class);
    $studentRepo->shouldReceive('getStudentById')->andReturn($student);
    $studentRepo->shouldReceive('findNextStudentIdInList')->andReturn(null);
    $studentRepo->shouldReceive('findPreviousStudentIdInList')->andReturn(null);
    app()->instance(StudentQueryRepository::class, $studentRepo);

    $recordQuery = Mockery::mock(RecordQueryRepository::class);
    $recordQuery->shouldReceive('listByStudentId')->andReturn([]);
    app()->instance(RecordQueryRepository::class, $recordQuery);

    $repo = Mockery::mock(RecordCommandRepository::class);
    $repo->shouldReceive('update')->once()->with(
        9,
        '188',
        '2026-03-15',
        'مديرية التربية',
        'تأييد استمرار',
        'ملاحظة محدثة'
    );
    app()->instance(RecordCommandRepository::class, $repo);

    $this->put(route('students.documents.update', ['id' => 5, 'recordId' => 9]), [
        'document_number' => '١٨٨',
        'document_date' => '2026-03-15',
        'addressee' => 'مديرية التربية',
        'purpose' => 'تأييد استمرار',
        'notes' => 'ملاحظة محدثة',
    ])->assertRedirect(route('students.documents.index', ['id' => 5]));
});
