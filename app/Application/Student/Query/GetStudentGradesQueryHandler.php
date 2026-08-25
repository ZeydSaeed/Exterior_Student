<?php

namespace App\Application\Student\Query;

use App\Application\Student\DTO\StudentGradesDTO;
use App\Domain\Student\StudentGradesView;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

/**
 * معالج استعلام درجات طالب واحد (للمودال)
 * يدمج كتالوج المواد حسب الفرع/الاختصاص مع الدرجات المخزنة
 */
final class GetStudentGradesQueryHandler
{
    public function __construct(
        private StudentQueryRepository $repository,
        private SubjectCatalogInterface $subjectCatalog
    ) {}

    public function handle(int $id): ?StudentGradesDTO
    {
        $view = $this->repository->getGradesById($id);
        if ($view === null) {
            return null;
        }

        $grades = $this->mergeGradesWithCatalog($view);

        return $this->toDTO($view, $grades);
    }

    /**
     * دمج قائمة المواد من الكتالوج مع درجات الطالب من قاعدة البيانات (مطابقة باسم المادة)
     * كل مادة في الكتالوج تُعرض مع الدرجة من main_table إن وُجدت للمادة نفسها
     *
     * @return list<array{subject: string, score: string}>
     */
    private function mergeGradesWithCatalog(StudentGradesView $view): array
    {
        $subjects = $this->subjectCatalog->getSubjectsFor($view->branch, $view->major);
        $dbGrades = $view->grades;

        if (empty($subjects)) {
            return empty($dbGrades)
                ? [['subject' => '', 'score' => ''], ['subject' => '', 'score' => ''], ['subject' => '', 'score' => '']]
                : $dbGrades;
        }

        $scoreBySubject = $this->buildScoreMapFromDb($dbGrades);
        $merged = [];
        foreach ($subjects as $subject) {
            $score = $this->findScoreForSubject($subject, $scoreBySubject);
            $merged[] = ['subject' => $subject, 'score' => $score];
        }

        return $merged;
    }

    /**
     * بناء خريطة اسم المادة => الدرجة من بيانات قاعدة البيانات
     *
     * @param  array<int, array{subject: string, score: string}>  $dbGrades
     * @return array<string, string> مفتاحها اسم المادة مُنظّف
     */
    private function buildScoreMapFromDb(array $dbGrades): array
    {
        $map = [];
        foreach ($dbGrades as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = $this->normalizeSubjectName((string) ($row['subject'] ?? ''));
            $score = trim((string) ($row['score'] ?? ''));
            if ($name !== '') {
                $map[$name] = $score;
            }
        }

        return $map;
    }

    private function normalizeSubjectName(string $name): string
    {
        $t = trim(preg_replace('/\s+/u', ' ', $name));
        $t = str_replace(['أ', 'إ', 'آ', 'ى'], 'ا', $t);

        return $t;
    }

    private function findScoreForSubject(string $catalogSubject, array $scoreBySubject): string
    {
        $key = $this->normalizeSubjectName($catalogSubject);
        if ($key === '') {
            return '';
        }
        if (isset($scoreBySubject[$key])) {
            return $scoreBySubject[$key];
        }
        foreach ($scoreBySubject as $dbKey => $score) {
            if ($dbKey === $key || $this->subjectNamesMatch($dbKey, $key)) {
                return $score;
            }
        }

        return '';
    }

    private function subjectNamesMatch(string $a, string $b): bool
    {
        return $a === $b || trim($a) === trim($b);
    }

    private function toDTO(StudentGradesView $view, array $grades): StudentGradesDTO
    {
        return new StudentGradesDTO(
            id: $view->id,
            full_name: $view->fullName,
            name_student: $view->nameStudent,
            name_father: $view->nameFather,
            name_grandfather: $view->nameGrandfather,
            name_surname: $view->nameSurname,
            exam_number: $view->examNumber,
            birth_date: $view->birthDate,
            birth_place: $view->birthPlace,
            mother_full_name: $view->motherFullName,
            gender: $view->gender,
            branch: $view->branch,
            major: $view->major,
            academic_year: $view->academicYear,
            last_school: $view->lastSchool,
            middle_doc_number: $view->middleDocNumber,
            middle_doc_date: $view->middleDocDate,
            issuing_authority: $view->issuingAuthority,
            result: $view->result,
            grades: $grades,
            total: $view->total,
            average: $view->average,
            round: $view->round,
            enrollment_number: $view->enrollmentNumber,
        );
    }
}
