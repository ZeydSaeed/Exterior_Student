<?php

use App\Application\Student\Query\GetStudentGradesQueryHandler;
use App\Domain\Student\StudentGradesView;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

it('includes the enrollment number in the grades dto', function () {
    $view = new StudentGradesView(
        id: 1,
        fullName: 'أحمد محمد علي حسين',
        nameStudent: 'أحمد',
        nameFather: 'محمد',
        nameGrandfather: 'علي',
        nameSurname: 'حسين',
        examNumber: '12345',
        birthDate: '2006-06-15',
        birthPlace: 'بغداد',
        motherFullName: 'فاطمة',
        gender: 'ذكر',
        branch: 'الصناعي',
        major: 'سيارات',
        academicYear: '2024-2025',
        lastSchool: '',
        middleDocNumber: '',
        middleDocDate: '',
        issuingAuthority: '',
        result: '',
        grades: [['subject' => 'الرياضيات', 'score' => '80']],
        total: '80',
        average: '80',
        round: '',
        enrollmentNumber: '8821',
    );

    $repository = Mockery::mock(StudentQueryRepository::class);
    $repository->shouldReceive('getGradesById')->once()->with(1)->andReturn($view);

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('الصناعي', 'سيارات')->andReturn([]);

    $dto = (new GetStudentGradesQueryHandler($repository, $catalog))->handle(1);

    expect($dto)->not->toBeNull()
        ->and($dto->enrollment_number)->toBe('8821')
        ->and($dto->toArray()['enrollment_number'])->toBe('8821');
});
