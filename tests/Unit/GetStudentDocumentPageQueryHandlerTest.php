<?php

use App\Application\Certificate\Query\GetCertificateSignatureEmployeesQueryHandler;
use App\Application\Service\NumberToArabicWordsConverter;
use App\Application\Student\Query\GetStudentDocumentPageQueryHandler;
use App\Domain\Certificate\CertificateSignatureRepository;
use App\Domain\Employee\EmployeeQueryRepository;
use App\Domain\Record\RecordQueryRepository;
use App\Domain\Student\StudentDocumentInfo;
use App\Domain\Student\StudentGradesView;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

function makeDocumentPageQueryHandler(
    StudentQueryRepository $studentRepository,
    RecordQueryRepository $recordRepository,
    SubjectCatalogInterface $subjectCatalog,
): GetStudentDocumentPageQueryHandler {
    return new GetStudentDocumentPageQueryHandler(
        $studentRepository,
        $recordRepository,
        $subjectCatalog,
        new NumberToArabicWordsConverter,
        new GetCertificateSignatureEmployeesQueryHandler(
            Mockery::mock(CertificateSignatureRepository::class),
            Mockery::mock(EmployeeQueryRepository::class),
        ),
    );
}

it('shows islamic education before arabic in industrial branch document grades table', function () {
    $studentRepository = Mockery::mock(StudentQueryRepository::class);
    $studentRepository->shouldReceive('getStudentDocumentInfo')->once()->with(1)->andReturn(new StudentDocumentInfo(
        fullName: 'طالب',
        examNumber: '12345',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherName: 'ام',
        branch: 'الصناعي',
        specialization: 'كهرباء',
        lastSchool: 'مدرسة',
        middleDocNumber: '1',
        middleDocDate: '2015-01-01',
        issuingAuthority: 'كربلاء',
        academicYear: '2025-2026',
        result: 'ناجح',
        round: 'الاول',
        gender: 'ذكر',
        pageNumber: '1',
        enrollmentNumber: '1',
    ));
    $studentRepository->shouldReceive('getGradesById')->once()->with(1)->andReturn(new StudentGradesView(
        id: 1,
        fullName: 'طالب',
        nameStudent: 'طالب',
        nameFather: 'اب',
        nameGrandfather: 'جد',
        nameSurname: 'ل',
        examNumber: '12345',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherFullName: 'ام',
        gender: 'ذكر',
        branch: 'الصناعي',
        major: 'كهرباء',
        academicYear: '2025-2026',
        lastSchool: '',
        middleDocNumber: '',
        middleDocDate: '',
        issuingAuthority: '',
        result: 'ناجح',
        grades: [
            ['subject' => 'اللغة العربية', 'score' => '80'],
            ['subject' => 'التربية الاسلامية', 'score' => '70'],
        ],
        total: '150',
        average: '75',
        round: 'الاول',
    ));

    $subjectCatalog = Mockery::mock(SubjectCatalogInterface::class);
    $subjectCatalog->shouldReceive('getSubjectsFor')->once()->with('الصناعي', 'كهرباء')->andReturn([
        'التربية الاسلامية',
        'اللغة العربية',
        'اللغة الانكليزية',
    ]);

    $recordRepository = Mockery::mock(RecordQueryRepository::class);
    $recordRepository->shouldReceive('listByStudentId')->once()->with(1)->andReturn([]);

    $handler = makeDocumentPageQueryHandler($studentRepository, $recordRepository, $subjectCatalog);

    $dto = $handler->handle(1, [
        ['type' => 'مدير', 'name' => 'مدير'],
        ['type' => 'موظف', 'name' => 'موظف'],
    ]);

    expect($dto->gradesTable[0]['subject'])->toBe('التربية الاسلامية')
        ->and($dto->gradesTable[0]['score'])->toBe('70')
        ->and($dto->gradesTable[1]['subject'])->toBe('اللغة العربية')
        ->and($dto->gradesTable[1]['score'])->toBe('80');
});

it('keeps catalog subject order for non industrial branch document grades table', function () {
    $studentRepository = Mockery::mock(StudentQueryRepository::class);
    $studentRepository->shouldReceive('getStudentDocumentInfo')->once()->with(2)->andReturn(new StudentDocumentInfo(
        fullName: 'طالب',
        examNumber: '12345',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherName: 'ام',
        branch: 'التجاري',
        specialization: 'ادارة',
        lastSchool: 'مدرسة',
        middleDocNumber: '1',
        middleDocDate: '2015-01-01',
        issuingAuthority: 'كربلاء',
        academicYear: '2025-2026',
        result: 'ناجح',
        round: 'الاول',
        gender: 'ذكر',
        pageNumber: '1',
        enrollmentNumber: '1',
    ));
    $studentRepository->shouldReceive('getGradesById')->once()->with(2)->andReturn(new StudentGradesView(
        id: 2,
        fullName: 'طالب',
        nameStudent: 'طالب',
        nameFather: 'اب',
        nameGrandfather: 'جد',
        nameSurname: 'ل',
        examNumber: '12345',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherFullName: 'ام',
        gender: 'ذكر',
        branch: 'التجاري',
        major: 'ادارة',
        academicYear: '2025-2026',
        lastSchool: '',
        middleDocNumber: '',
        middleDocDate: '',
        issuingAuthority: '',
        result: 'ناجح',
        grades: [
            ['subject' => 'القران الكريم والتربية الاسلامية', 'score' => '70'],
            ['subject' => 'اللغة العربية', 'score' => '80'],
        ],
        total: '150',
        average: '75',
        round: 'الاول',
    ));

    $subjectCatalog = Mockery::mock(SubjectCatalogInterface::class);
    $subjectCatalog->shouldReceive('getSubjectsFor')->once()->with('التجاري', 'ادارة')->andReturn([
        'القران الكريم والتربية الاسلامية',
        'اللغة العربية',
    ]);

    $recordRepository = Mockery::mock(RecordQueryRepository::class);
    $recordRepository->shouldReceive('listByStudentId')->once()->with(2)->andReturn([]);

    $handler = makeDocumentPageQueryHandler($studentRepository, $recordRepository, $subjectCatalog);

    $dto = $handler->handle(2, [
        ['type' => 'مدير', 'name' => 'مدير'],
        ['type' => 'موظف', 'name' => 'موظف'],
    ]);

    expect($dto->gradesTable[0]['subject'])->toBe('القران الكريم والتربية الاسلامية')
        ->and($dto->gradesTable[1]['subject'])->toBe('اللغة العربية');
});

it('shows islamic education on agricultural document grades table with matching scores', function () {
    $studentRepository = Mockery::mock(StudentQueryRepository::class);
    $studentRepository->shouldReceive('getStudentDocumentInfo')->once()->with(3)->andReturn(new StudentDocumentInfo(
        fullName: 'طالب',
        examNumber: '54321',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherName: 'ام',
        branch: 'الزراعي',
        specialization: 'زراعي',
        lastSchool: 'مدرسة',
        middleDocNumber: '1',
        middleDocDate: '2015-01-01',
        issuingAuthority: 'كربلاء',
        academicYear: '2025-2026',
        result: 'ناجح',
        round: 'الاول',
        gender: 'ذكر',
        pageNumber: '1',
        enrollmentNumber: '1',
    ));
    $studentRepository->shouldReceive('getGradesById')->once()->with(3)->andReturn(new StudentGradesView(
        id: 3,
        fullName: 'طالب',
        nameStudent: 'طالب',
        nameFather: 'اب',
        nameGrandfather: 'جد',
        nameSurname: 'ل',
        examNumber: '54321',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherFullName: 'ام',
        gender: 'ذكر',
        branch: 'الزراعي',
        major: 'زراعي',
        academicYear: '2025-2026',
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

    $subjectCatalog = Mockery::mock(SubjectCatalogInterface::class);
    $subjectCatalog->shouldReceive('getSubjectsFor')->once()->with('الزراعي', 'زراعي')->andReturn([
        'التربية الاسلامية',
        'اللغة العربية',
    ]);

    $recordRepository = Mockery::mock(RecordQueryRepository::class);
    $recordRepository->shouldReceive('listByStudentId')->once()->with(3)->andReturn([]);

    $handler = makeDocumentPageQueryHandler($studentRepository, $recordRepository, $subjectCatalog);

    $dto = $handler->handle(3, [
        ['type' => 'مدير', 'name' => 'مدير'],
        ['type' => 'موظف', 'name' => 'موظف'],
    ]);

    expect($dto->gradesTable[0]['subject'])->toBe('التربية الاسلامية')
        ->and($dto->gradesTable[0]['score'])->toBe('70')
        ->and($dto->gradesTable[1]['subject'])->toBe('اللغة العربية')
        ->and($dto->gradesTable[1]['score'])->toBe('80');
});

it('shows computer maintenance on computer assembly document grades table with matching scores', function () {
    $studentRepository = Mockery::mock(StudentQueryRepository::class);
    $studentRepository->shouldReceive('getStudentDocumentInfo')->once()->with(4)->andReturn(new StudentDocumentInfo(
        fullName: 'طالب',
        examNumber: '67890',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherName: 'ام',
        branch: 'الحاسوب وتقنية المعلومات',
        specialization: 'تجميع وصيانة الحاسوب',
        lastSchool: 'مدرسة',
        middleDocNumber: '1',
        middleDocDate: '2015-01-01',
        issuingAuthority: 'كربلاء',
        academicYear: '2025-2026',
        result: 'ناجح',
        round: 'الاول',
        gender: 'ذكر',
        pageNumber: '1',
        enrollmentNumber: '1',
    ));
    $studentRepository->shouldReceive('getGradesById')->once()->with(4)->andReturn(new StudentGradesView(
        id: 4,
        fullName: 'طالب',
        nameStudent: 'طالب',
        nameFather: 'اب',
        nameGrandfather: 'جد',
        nameSurname: 'ل',
        examNumber: '67890',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherFullName: 'ام',
        gender: 'ذكر',
        branch: 'الحاسوب وتقنية المعلومات',
        major: 'تجميع وصيانة الحاسوب',
        academicYear: '2025-2026',
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

    $subjectCatalog = Mockery::mock(SubjectCatalogInterface::class);
    $subjectCatalog->shouldReceive('getSubjectsFor')->once()->with('الحاسوب وتقنية المعلومات', 'تجميع وصيانة الحاسوب')->andReturn([
        'صيانة الحاسوب',
        'المعالجات الدقيقة',
    ]);

    $recordRepository = Mockery::mock(RecordQueryRepository::class);
    $recordRepository->shouldReceive('listByStudentId')->once()->with(4)->andReturn([]);

    $handler = makeDocumentPageQueryHandler($studentRepository, $recordRepository, $subjectCatalog);

    $dto = $handler->handle(4, [
        ['type' => 'مدير', 'name' => 'مدير'],
        ['type' => 'موظف', 'name' => 'موظف'],
    ]);

    expect($dto->gradesTable[0]['subject'])->toBe('صيانة الحاسوب')
        ->and($dto->gradesTable[0]['score'])->toBe('85')
        ->and($dto->gradesTable[1]['subject'])->toBe('المعالجات الدقيقة')
        ->and($dto->gradesTable[1]['score'])->toBe('70');
});

it('shows production and operations on management document grades table with matching scores', function () {
    $studentRepository = Mockery::mock(StudentQueryRepository::class);
    $studentRepository->shouldReceive('getStudentDocumentInfo')->once()->with(5)->andReturn(new StudentDocumentInfo(
        fullName: 'طالب',
        examNumber: '11223',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherName: 'ام',
        branch: 'التجاري',
        specialization: 'ادارة',
        lastSchool: 'مدرسة',
        middleDocNumber: '1',
        middleDocDate: '2015-01-01',
        issuingAuthority: 'كربلاء',
        academicYear: '2025-2026',
        result: 'ناجح',
        round: 'الاول',
        gender: 'ذكر',
        pageNumber: '1',
        enrollmentNumber: '1',
    ));
    $studentRepository->shouldReceive('getGradesById')->once()->with(5)->andReturn(new StudentGradesView(
        id: 5,
        fullName: 'طالب',
        nameStudent: 'طالب',
        nameFather: 'اب',
        nameGrandfather: 'جد',
        nameSurname: 'ل',
        examNumber: '11223',
        birthDate: '2000-01-01',
        birthPlace: 'كربلاء',
        motherFullName: 'ام',
        gender: 'ذكر',
        branch: 'التجاري',
        major: 'ادارة',
        academicYear: '2025-2026',
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

    $subjectCatalog = Mockery::mock(SubjectCatalogInterface::class);
    $subjectCatalog->shouldReceive('getSubjectsFor')->once()->with('التجاري', 'ادارة')->andReturn([
        'ادارة الانتاج والعمليات',
        'الادارة المالية',
    ]);

    $recordRepository = Mockery::mock(RecordQueryRepository::class);
    $recordRepository->shouldReceive('listByStudentId')->once()->with(5)->andReturn([]);

    $handler = makeDocumentPageQueryHandler($studentRepository, $recordRepository, $subjectCatalog);

    $dto = $handler->handle(5, [
        ['type' => 'مدير', 'name' => 'مدير'],
        ['type' => 'موظف', 'name' => 'موظف'],
    ]);

    expect($dto->gradesTable[0]['subject'])->toBe('ادارة الانتاج والعمليات')
        ->and($dto->gradesTable[0]['score'])->toBe('88')
        ->and($dto->gradesTable[1]['subject'])->toBe('الادارة المالية')
        ->and($dto->gradesTable[1]['score'])->toBe('75');
});
