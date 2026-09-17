<?php

use App\Application\Service\NumberToArabicWordsConverter;
use App\Application\Student\Query\GetStudentCertificateWithGradesQueryHandler;
use App\Domain\Student\StudentCertificate;
use App\Domain\Student\StudentGradesView;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\StudentReadRepository;
use App\Domain\Student\SubjectCatalogInterface;

it('shows islamic education then arabic on industrial certificate grades with matching scores', function () {
    $student = new StudentCertificate(
        firstName: 'طالب',
        fatherName: 'محمد',
        grandName: 'علي',
        lastName: 'حسين',
        examNumberValue: '12345',
        birthDate: '2006-06-15',
        branch: 'الصناعي',
        specialization: 'كهرباء',
        academicYear: '2024-2025',
        result: 'ناجح',
        round: 'الاول',
        average: '70',
        gender: 'ذكر',
    );

    $readRepository = Mockery::mock(StudentReadRepository::class);
    $readRepository->shouldReceive('findById')->once()->with(1)->andReturn($student);

    $queryRepository = Mockery::mock(StudentQueryRepository::class);
    $queryRepository->shouldReceive('getGradesById')->once()->with(1)->andReturn(new StudentGradesView(
        id: 1,
        fullName: 'طالب محمد علي حسين',
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
        major: 'كهرباء',
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
    ));

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('الصناعي', 'كهرباء')->andReturn([
        'التربية الاسلامية',
        'اللغة العربية',
        'اللغة الانكليزية',
    ]);

    $dto = (new GetStudentCertificateWithGradesQueryHandler(
        $readRepository,
        $queryRepository,
        $catalog,
        new NumberToArabicWordsConverter,
    ))->handle(1, []);

    expect($dto->gradesTable[0]['subject'])->toBe('التربية الاسلامية')
        ->and($dto->gradesTable[0]['score'])->toBe('70')
        ->and($dto->gradesTable[1]['subject'])->toBe('اللغة العربية')
        ->and($dto->gradesTable[1]['score'])->toBe('80')
        ->and($dto->gradesTable[2]['subject'])->toBe('اللغة الانكليزية')
        ->and($dto->gradesTable[2]['score'])->toBe('60');
});

it('shows islamic education on agricultural certificate grades with matching scores', function () {
    $student = new StudentCertificate(
        firstName: 'طالب',
        fatherName: 'محمد',
        grandName: 'علي',
        lastName: 'حسين',
        examNumberValue: '54321',
        birthDate: '2006-06-15',
        branch: 'الزراعي',
        specialization: 'زراعي',
        academicYear: '2024-2025',
        result: 'ناجح',
        round: 'الاول',
        average: '75',
        gender: 'ذكر',
    );

    $readRepository = Mockery::mock(StudentReadRepository::class);
    $readRepository->shouldReceive('findById')->once()->with(2)->andReturn($student);

    $queryRepository = Mockery::mock(StudentQueryRepository::class);
    $queryRepository->shouldReceive('getGradesById')->once()->with(2)->andReturn(new StudentGradesView(
        id: 2,
        fullName: 'طالب محمد علي حسين',
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
    ));

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('الزراعي', 'زراعي')->andReturn([
        'التربية الاسلامية',
        'اللغة العربية',
    ]);

    $dto = (new GetStudentCertificateWithGradesQueryHandler(
        $readRepository,
        $queryRepository,
        $catalog,
        new NumberToArabicWordsConverter,
    ))->handle(2, []);

    expect($dto->gradesTable[0]['subject'])->toBe('التربية الاسلامية')
        ->and($dto->gradesTable[0]['score'])->toBe('70')
        ->and($dto->gradesTable[1]['subject'])->toBe('اللغة العربية')
        ->and($dto->gradesTable[1]['score'])->toBe('80');
});

it('shows computer maintenance on computer assembly certificate grades with matching scores', function () {
    $student = new StudentCertificate(
        firstName: 'طالب',
        fatherName: 'محمد',
        grandName: 'علي',
        lastName: 'حسين',
        examNumberValue: '67890',
        birthDate: '2006-06-15',
        branch: 'الحاسوب وتقنية المعلومات',
        specialization: 'تجميع وصيانة الحاسوب',
        academicYear: '2024-2025',
        result: 'ناجح',
        round: 'الاول',
        average: '77',
        gender: 'ذكر',
    );

    $readRepository = Mockery::mock(StudentReadRepository::class);
    $readRepository->shouldReceive('findById')->once()->with(3)->andReturn($student);

    $queryRepository = Mockery::mock(StudentQueryRepository::class);
    $queryRepository->shouldReceive('getGradesById')->once()->with(3)->andReturn(new StudentGradesView(
        id: 3,
        fullName: 'طالب محمد علي حسين',
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
    ));

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('الحاسوب وتقنية المعلومات', 'تجميع وصيانة الحاسوب')->andReturn([
        'صيانة الحاسوب',
        'المعالجات الدقيقة',
    ]);

    $dto = (new GetStudentCertificateWithGradesQueryHandler(
        $readRepository,
        $queryRepository,
        $catalog,
        new NumberToArabicWordsConverter,
    ))->handle(3, []);

    expect($dto->gradesTable[0]['subject'])->toBe('صيانة الحاسوب')
        ->and($dto->gradesTable[0]['score'])->toBe('85')
        ->and($dto->gradesTable[1]['subject'])->toBe('المعالجات الدقيقة')
        ->and($dto->gradesTable[1]['score'])->toBe('70');
});

it('shows production and operations on management certificate grades with matching scores', function () {
    $student = new StudentCertificate(
        firstName: 'طالب',
        fatherName: 'محمد',
        grandName: 'علي',
        lastName: 'حسين',
        examNumberValue: '11223',
        birthDate: '2006-06-15',
        branch: 'التجاري',
        specialization: 'ادارة',
        academicYear: '2024-2025',
        result: 'ناجح',
        round: 'الاول',
        average: '81',
        gender: 'ذكر',
    );

    $readRepository = Mockery::mock(StudentReadRepository::class);
    $readRepository->shouldReceive('findById')->once()->with(4)->andReturn($student);

    $queryRepository = Mockery::mock(StudentQueryRepository::class);
    $queryRepository->shouldReceive('getGradesById')->once()->with(4)->andReturn(new StudentGradesView(
        id: 4,
        fullName: 'طالب محمد علي حسين',
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
    ));

    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')->once()->with('التجاري', 'ادارة')->andReturn([
        'ادارة الانتاج والعمليات',
        'الادارة المالية',
    ]);

    $dto = (new GetStudentCertificateWithGradesQueryHandler(
        $readRepository,
        $queryRepository,
        $catalog,
        new NumberToArabicWordsConverter,
    ))->handle(4, []);

    expect($dto->gradesTable[0]['subject'])->toBe('ادارة الانتاج والعمليات')
        ->and($dto->gradesTable[0]['score'])->toBe('88')
        ->and($dto->gradesTable[1]['subject'])->toBe('الادارة المالية')
        ->and($dto->gradesTable[1]['score'])->toBe('75');
});
