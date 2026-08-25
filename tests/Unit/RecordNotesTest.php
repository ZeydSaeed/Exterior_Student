<?php

use App\Application\Record\Command\CreateRecordCommandHandler;
use App\Application\Record\Command\UpdateRecordCommandHandler;
use App\Domain\Record\Record;
use App\Domain\Record\RecordCommandRepository;

it('maps notes from a document row', function () {
    $record = Record::fromRow((object) [
        'id' => 4,
        'student_id' => 9,
        'document_number' => '188',
        'document_date' => '2026-08-25',
        'addressee' => 'مديرية التربية',
        'purpose' => 'تأييد',
        'notes' => 'ملاحظة طويلة عن الوثيقة',
    ]);

    expect($record->notes)->toBe('ملاحظة طويلة عن الوثيقة');
});

it('passes notes to the repository when creating a document', function () {
    $repository = Mockery::mock(RecordCommandRepository::class);
    $repository->shouldReceive('create')
        ->once()
        ->with(1, '188', '2026-08-25', 'جهة', 'غرض', 'ملاحظة الحفظ');

    (new CreateRecordCommandHandler($repository))->handle(
        studentId: 1,
        documentNumber: '188',
        documentDate: '2026-08-25',
        addressee: 'جهة',
        purpose: 'غرض',
        notes: 'ملاحظة الحفظ',
    );
});

it('passes notes to the repository when updating a document', function () {
    $repository = Mockery::mock(RecordCommandRepository::class);
    $repository->shouldReceive('update')
        ->once()
        ->with(10, '188', '2026-08-25', 'جهة', 'غرض', 'ملاحظة محدثة');

    (new UpdateRecordCommandHandler($repository))->handle(
        recordId: 10,
        documentNumber: '188',
        documentDate: '2026-08-25',
        addressee: 'جهة',
        purpose: 'غرض',
        notes: 'ملاحظة محدثة',
    );
});
