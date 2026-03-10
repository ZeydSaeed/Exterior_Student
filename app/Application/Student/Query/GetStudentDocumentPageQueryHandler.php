<?php

namespace App\Application\Student\Query;

use App\Application\Certificate\Query\GetCertificateSignatureEmployeesQueryHandler;
use App\Application\Record\DTO\RecordDTO;
use App\Application\Service\NumberToArabicWordsConverter;
use App\Application\Student\DTO\StudentDocumentPageDTO;
use App\Domain\Record\RecordQueryRepository;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

/**
 * Use Case: جلب بيانات صفحة سجل قيد الطالب (قيد الطالب) — CQRS Query.
 * يعيد معلومات الطالب الكاملة + جدول الدرجات حسب الفرع/الاختصاص + الدروس التي أكمل بها + الوثائق + الموظفين.
 */
final class GetStudentDocumentPageQueryHandler
{
    public function __construct(
        private StudentQueryRepository $studentRepository,
        private RecordQueryRepository $recordRepository,
        private SubjectCatalogInterface $subjectCatalog,
        private NumberToArabicWordsConverter $numberToWords,
        private GetCertificateSignatureEmployeesQueryHandler $signatureEmployeesHandler,
    ) {}

    public function handle(int $id): StudentDocumentPageDTO
    {
        $info = $this->studentRepository->getStudentDocumentInfo($id);
        if ($info === null) {
            throw new \RuntimeException('Student not found for document page.', 404);
        }

        $gradesView = $this->studentRepository->getGradesById($id);
        $subjects = $this->subjectCatalog->getSubjectsFor($info->branch, $info->specialization);

        $scoreBySubject = $this->buildScoreMap($gradesView);
        $gradesTable = [];
        $subjectsCompleted = [];
        $totalNumeric = 0;

        foreach ($subjects as $subjectName) {
            $score = $this->findScoreForSubject($subjectName, $scoreBySubject);
            $scoreTrim = trim($score);
            $scoreInt = $scoreTrim !== '' && is_numeric($scoreTrim) ? (int) round((float) $scoreTrim) : 0;
            $totalNumeric += $scoreInt;
            if ($scoreInt < 50) {
                $subjectsCompleted[] = $subjectName;
            }
            $gradesTable[] = [
                'subject'     => $subjectName,
                'score'       => $scoreTrim !== '' ? $scoreTrim : '0',
                'score_words' => $scoreTrim !== '' && is_numeric($scoreTrim)
                    ? $this->numberToWords->convert((int) round((float) $scoreTrim))
                    : 'صفر',
            ];
        }

        $totalFromDb = $gradesView !== null ? trim($gradesView->total) : '';
        if ($totalFromDb !== '' && is_numeric($totalFromDb)) {
            $totalNumeric = (int) round((float) $totalFromDb);
        }
        $totalStr = (string) $totalNumeric;
        $totalWords = $this->numberToWords->convert($totalStr);

        $records = $this->recordRepository->listByStudentId($id);
        $documentDTOs = array_map(
            static fn ($r) => new RecordDTO(
                id: $r->id,
                documentNumber: $r->documentNumber,
                documentDate: $r->documentDate,
                addressee: $r->addressee,
                purpose: $r->purpose,
            ),
            $records
        );

        $employees = $this->signatureEmployeesHandler->handle();

        return new StudentDocumentPageDTO(
            studentId: $id,
            fullName: $info->fullName,
            examNumber: $info->examNumber,
            birthDate: $info->birthDate,
            birthPlace: $info->birthPlace,
            motherName: $info->motherName,
            branch: $info->branch,
            specialization: $info->specialization,
            lastSchool: $info->lastSchool,
            middleDocNumber: $info->middleDocNumber,
            middleDocDate: $info->middleDocDate,
            issuingAuthority: $info->issuingAuthority,
            academicYear: $info->academicYear,
            result: $info->result,
            round: $info->round,
            gender: $info->gender,
            gradesTable: $gradesTable,
            total: $totalStr,
            totalWords: $totalWords,
            subjectsCompleted: $subjectsCompleted,
            documents: $documentDTOs,
            employees: $employees,
        );
    }

    /**
     * @return array<string, string>
     */
    private function buildScoreMap(?\App\Domain\Student\StudentGradesView $gradesView): array
    {
        $map = [];
        if ($gradesView === null) {
            return $map;
        }
        foreach ($gradesView->grades as $row) {
            if (!is_array($row)) {
                continue;
            }
            $subject = trim((string) ($row['subject'] ?? ''));
            $score = trim((string) ($row['score'] ?? ''));
            if ($subject !== '') {
                $map[$subject] = $score;
            }
        }
        return $map;
    }

    private function findScoreForSubject(string $subjectName, array $scoreBySubject): string
    {
        if ($subjectName !== '' && isset($scoreBySubject[$subjectName])) {
            return $scoreBySubject[$subjectName];
        }
        $key = $this->normalize($subjectName);
        if ($key === '') {
            return '';
        }
        foreach ($scoreBySubject as $dbSubject => $score) {
            if ($this->normalize($dbSubject) === $key) {
                return $score;
            }
        }
        return '';
    }

    private function normalize(string $name): string
    {
        $t = trim(preg_replace('/\s+/u', ' ', $name));
        $t = str_replace(['أ', 'إ', 'آ', 'ى'], 'ا', $t);
        return $t;
    }
}
