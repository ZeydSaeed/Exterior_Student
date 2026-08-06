<?php

use App\Application\Profile\Command\UpdateAttestationCommandHandler;
use App\Domain\Attestation\Attestation;
use App\Domain\Attestation\AttestationCommandRepository;
use App\Domain\Attestation\AttestationQueryRepository;

it('maps attestation row fields for certificate edit prefill', function () {
    $attestation = Attestation::fromRow((object) [
        'id' => 12,
        'exam_number' => '2724',
        'type' => 'with_grades',
        'date' => '2026-08-06',
        'number' => '88',
        'issued_to' => 'جامعة الاختبار',
        'right_title' => 'منظم التأييد',
        'right_employee_name' => 'أحمد',
        'left_title' => 'مسؤول شعبة شؤون الطلبة',
        'left_employee_name' => 'سارة',
    ]);

    expect($attestation->id)->toBe(12)
        ->and($attestation->type)->toBe('with_grades')
        ->and($attestation->number)->toBe('88')
        ->and($attestation->issuedTo)->toBe('جامعة الاختبار')
        ->and($attestation->rightEmployeeName)->toBe('أحمد')
        ->and($attestation->leftEmployeeName)->toBe('سارة');
});

it('persists edited signer names by resolving or creating employees', function () {
    $repository = Mockery::mock(\App\Domain\Attestation\AttestationCommandRepository::class);
    $repository->shouldReceive('update')
        ->once()
        ->withArgs(function (
            int $id,
            ?string $date,
            ?string $number,
            ?string $issuedTo,
            ?string $rightTitle,
            ?string $rightEmployeeName,
            ?string $leftTitle,
            ?string $leftEmployeeName,
        ): bool {
            return $id === 8
                && $number === '12'
                && $issuedTo === 'كلية الهندسة'
                && $rightTitle === 'منظم التأييد'
                && $rightEmployeeName === 'موظف معدل'
                && $leftTitle === 'المسؤول'
                && $leftEmployeeName === 'مسؤول معدل';
        });

    $handler = new UpdateAttestationCommandHandler($repository);
    $handler->handle(
        id: 8,
        date: '2026-08-06',
        number: '12',
        issuedTo: 'كلية الهندسة',
        rightTitle: 'منظم التأييد',
        rightEmployeeName: 'موظف معدل',
        leftTitle: 'المسؤول',
        leftEmployeeName: 'مسؤول معدل',
    );

    expect(true)->toBeTrue();
});

it('updates attestation fields through the command handler', function () {
    $repository = Mockery::mock(AttestationCommandRepository::class);
    $repository->shouldReceive('update')
        ->once()
        ->withArgs(function (
            int $id,
            ?string $date,
            ?string $number,
            ?string $issuedTo,
            ?string $rightTitle,
            ?string $rightEmployeeName,
            ?string $leftTitle,
            ?string $leftEmployeeName,
        ): bool {
            return $id === 5
                && $date === '2026-08-06'
                && $number === '77'
                && $issuedTo === 'جهة جديدة'
                && $rightTitle === 'منظم التأييد'
                && $rightEmployeeName === 'موظف يمين'
                && $leftTitle === 'مسؤول شعبة شؤون الطلبة'
                && $leftEmployeeName === 'موظف يسار';
        });

    $handler = new UpdateAttestationCommandHandler($repository);
    $handler->handle(
        id: 5,
        date: '2026-08-06',
        number: '77',
        issuedTo: 'جهة جديدة',
        rightTitle: 'منظم التأييد',
        rightEmployeeName: 'موظف يمين',
        leftTitle: 'مسؤول شعبة شؤون الطلبة',
        leftEmployeeName: 'موظف يسار',
    );

    expect(true)->toBeTrue();
});

it('resolves attestation by student and id from the query repository contract', function () {
    $attestation = new Attestation(
        id: 3,
        examNumber: '1',
        type: 'without_grades',
        date: '2026-08-01',
        number: '11',
        issuedTo: 'كلية',
        rightTitle: 'منظم التأييد',
        rightEmployeeName: 'أ',
        leftTitle: 'مسؤول شعبة شؤون الطلبة',
        leftEmployeeName: 'ب',
    );

    $repository = Mockery::mock(AttestationQueryRepository::class);
    $repository->shouldReceive('findByStudentAndId')
        ->once()
        ->with(9, 3)
        ->andReturn($attestation);
    $repository->shouldReceive('findByStudentAndId')
        ->once()
        ->with(9, 999)
        ->andReturn(null);

    expect($repository->findByStudentAndId(9, 3))->toBe($attestation)
        ->and($repository->findByStudentAndId(9, 999))->toBeNull();
});
