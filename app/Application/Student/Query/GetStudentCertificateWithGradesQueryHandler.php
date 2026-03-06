<?php

namespace App\Application\Student\Query;

use App\Application\Service\NumberToArabicWordsConverter;
use App\Application\Student\DTO\StudentCertificateWithGradesDTO;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\StudentReadRepository;
use App\Domain\Student\SubjectCatalogInterface;

/**
 * Use Case: جلب بيانات طالب لعرض صفحة التأييد بالدرجات (قراءة فقط — CQRS Query).
 * نفس معمارية التأييد بدون درجات + جدول الدرجات والمجموع كتابة.
 */
final class GetStudentCertificateWithGradesQueryHandler
{
    public function __construct(
        private StudentReadRepository $readRepository,
        private StudentQueryRepository $queryRepository,
        private SubjectCatalogInterface $subjectCatalog,
        private NumberToArabicWordsConverter $numberToWords,
    ) {}

    /**
     * @param array<int, array{type: string, name: string}> $employees الموظفون من الجلسة
     */
    public function handle(int $id, array $employees): StudentCertificateWithGradesDTO
    {
        $student = $this->readRepository->findById($id);
        if ($student === null) {
            throw new \RuntimeException('Student not found for certificate with grades.', 404);
        }

        $gradesView = $this->queryRepository->getGradesById($id);
        $subjects = $this->subjectCatalog->getSubjectsFor($student->branch(), $student->specialization());

        $scoreBySubject = $this->buildScoreMap($gradesView);
        $gradesTable = [];
        $totalNumeric = 0;
        foreach ($subjects as $subjectName) {
            $score = $this->findScoreForSubject($subjectName, $scoreBySubject);
            $scoreTrim = trim($score);
            $scoreInt = $scoreTrim !== '' && is_numeric($scoreTrim) ? (int) round((float) $scoreTrim) : 0;
            $totalNumeric += $scoreInt;
            $gradesTable[] = [
                'subject'      => $subjectName,
                'score'        => $score,
                'score_words'  => $scoreTrim !== '' && is_numeric($scoreTrim) ? $this->numberToWords->convert($scoreInt) : '',
            ];
        }

        $totalFromDb = $gradesView !== null ? trim($gradesView->total) : '';
        if ($totalFromDb !== '' && is_numeric($totalFromDb)) {
            $totalNumeric = (int) round((float) $totalFromDb);
        }
        $totalStr = (string) $totalNumeric;
        $totalWords = $this->numberToWords->convert($totalStr);

        return new StudentCertificateWithGradesDTO(
            fullName: $student->fullName(),
            examNumber: $student->examNumber(),
            birthDate: $student->birthDate(),
            branch: $student->branch(),
            specialization: $student->specialization(),
            academicYear: $student->academicYear(),
            result: $student->result(),
            round: $student->round(),
            gender: $student->gender(),
            employees: $employees,
            gradesTable: $gradesTable,
            total: $totalStr,
            totalWords: $totalWords,
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
