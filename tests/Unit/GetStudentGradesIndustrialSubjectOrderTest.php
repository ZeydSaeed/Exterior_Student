<?php

use App\Application\Student\Query\GetStudentGradesQueryHandler;
use App\Domain\Student\StudentGradesView;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

it('shows islamic education then arabic on industrial dashboard grades with matching scores', function () {
    $view = new StudentGradesView(
        id: 1,
        fullName: 'طالب صناعي',
        nameStudent: 'طالب',
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
        result: 'ناجح',
        grades: [
            ['subject' => 'اللغة العربية', 'score' => '80'],
            ['subject' => 'التربية الاسلامية', 'score' => '70'],
            ['subject' => 'اللغة الانكليزية', 'score' => '60'],
        ],
        total: '210',
        average: '70',
        round: 'الاول',
        enrollmentNumber: '1',
    );

    $repository = Mockery::mock(StudentQueryRepository::class);
    $repository->shouldReceive('getGradesById')->once()->with(1)->andReturn($view);

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('الصناعي', 'سيارات')->andReturn([
        'التربية الاسلامية',
        'اللغة العربية',
        'اللغة الانكليزية',
    ]);

    $dto = (new GetStudentGradesQueryHandler($repository, $catalog))->handle(1);

    expect($dto)->not->toBeNull()
        ->and($dto->grades[0]['subject'])->toBe('التربية الاسلامية')
        ->and($dto->grades[0]['score'])->toBe('70')
        ->and($dto->grades[1]['subject'])->toBe('اللغة العربية')
        ->and($dto->grades[1]['score'])->toBe('80')
        ->and($dto->grades[2]['subject'])->toBe('اللغة الانكليزية')
        ->and($dto->grades[2]['score'])->toBe('60');
});

it('shows islamic education on agricultural dashboard grades with matching scores', function () {
    $view = new StudentGradesView(
        id: 2,
        fullName: 'طالب زراعي',
        nameStudent: 'طالب',
        nameFather: 'محمد',
        nameGrandfather: 'علي',
        nameSurname: 'حسين',
        examNumber: '54321',
        birthDate: '2006-06-15',
        birthPlace: 'كربلاء',
        motherFullName: 'فاطمة',
        gender: 'ذكر',
        branch: 'الزراعي',
        major: 'زراعي',
        academicYear: '2024-2025',
        lastSchool: '',
        middleDocNumber: '',
        middleDocDate: '',
        issuingAuthority: '',
        result: 'ناجح',
        grades: [
            ['subject' => 'التربية الاسلامية', 'score' => '70'],
            ['subject' => 'اللغة العربية', 'score' => '80'],
        ],
        total: '150',
        average: '75',
        round: 'الاول',
        enrollmentNumber: '1',
    );

    $repository = Mockery::mock(StudentQueryRepository::class);
    $repository->shouldReceive('getGradesById')->once()->with(2)->andReturn($view);

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('الزراعي', 'زراعي')->andReturn([
        'التربية الاسلامية',
        'اللغة العربية',
    ]);

    $dto = (new GetStudentGradesQueryHandler($repository, $catalog))->handle(2);

    expect($dto)->not->toBeNull()
        ->and($dto->grades[0]['subject'])->toBe('التربية الاسلامية')
        ->and($dto->grades[0]['score'])->toBe('70')
        ->and($dto->grades[1]['subject'])->toBe('اللغة العربية')
        ->and($dto->grades[1]['score'])->toBe('80');
});

it('shows computer maintenance on computer assembly dashboard grades with matching scores', function () {
    $view = new StudentGradesView(
        id: 3,
        fullName: 'طالب حاسوب',
        nameStudent: 'طالب',
        nameFather: 'محمد',
        nameGrandfather: 'علي',
        nameSurname: 'حسين',
        examNumber: '67890',
        birthDate: '2006-06-15',
        birthPlace: 'كربلاء',
        motherFullName: 'فاطمة',
        gender: 'ذكر',
        branch: 'الحاسوب وتقنية المعلومات',
        major: 'تجميع وصيانة الحاسوب',
        academicYear: '2024-2025',
        lastSchool: '',
        middleDocNumber: '',
        middleDocDate: '',
        issuingAuthority: '',
        result: 'ناجح',
        grades: [
            ['subject' => 'صيانة الحاسوب', 'score' => '85'],
            ['subject' => 'المعالجات الدقيقة', 'score' => '70'],
        ],
        total: '155',
        average: '77',
        round: 'الاول',
        enrollmentNumber: '1',
    );

    $repository = Mockery::mock(StudentQueryRepository::class);
    $repository->shouldReceive('getGradesById')->once()->with(3)->andReturn($view);

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('الحاسوب وتقنية المعلومات', 'تجميع وصيانة الحاسوب')->andReturn([
        'صيانة الحاسوب',
        'المعالجات الدقيقة',
    ]);

    $dto = (new GetStudentGradesQueryHandler($repository, $catalog))->handle(3);

    expect($dto)->not->toBeNull()
        ->and($dto->grades[0]['subject'])->toBe('صيانة الحاسوب')
        ->and($dto->grades[0]['score'])->toBe('85')
        ->and($dto->grades[1]['subject'])->toBe('المعالجات الدقيقة')
        ->and($dto->grades[1]['score'])->toBe('70');
});

it('shows production and operations on management dashboard grades with matching scores', function () {
    $view = new StudentGradesView(
        id: 4,
        fullName: 'طالب ادارة',
        nameStudent: 'طالب',
        nameFather: 'محمد',
        nameGrandfather: 'علي',
        nameSurname: 'حسين',
        examNumber: '11223',
        birthDate: '2006-06-15',
        birthPlace: 'كربلاء',
        motherFullName: 'فاطمة',
        gender: 'ذكر',
        branch: 'التجاري',
        major: 'ادارة',
        academicYear: '2024-2025',
        lastSchool: '',
        middleDocNumber: '',
        middleDocDate: '',
        issuingAuthority: '',
        result: 'ناجح',
        grades: [
            ['subject' => 'ادارة الانتاج والعمليات', 'score' => '88'],
            ['subject' => 'الادارة المالية', 'score' => '75'],
        ],
        total: '163',
        average: '81',
        round: 'الاول',
        enrollmentNumber: '1',
    );

    $repository = Mockery::mock(StudentQueryRepository::class);
    $repository->shouldReceive('getGradesById')->once()->with(4)->andReturn($view);

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('التجاري', 'ادارة')->andReturn([
        'ادارة الانتاج والعمليات',
        'الادارة المالية',
    ]);

    $dto = (new GetStudentGradesQueryHandler($repository, $catalog))->handle(4);

    expect($dto)->not->toBeNull()
        ->and($dto->grades[0]['subject'])->toBe('ادارة الانتاج والعمليات')
        ->and($dto->grades[0]['score'])->toBe('88')
        ->and($dto->grades[1]['subject'])->toBe('الادارة المالية')
        ->and($dto->grades[1]['score'])->toBe('75');
});
