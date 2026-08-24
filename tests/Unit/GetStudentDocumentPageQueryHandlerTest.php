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
        'اللغة العربية',
        'التربية الاسلامية',
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
