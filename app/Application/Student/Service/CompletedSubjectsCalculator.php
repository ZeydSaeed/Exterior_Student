<?php

namespace App\Application\Student\Service;

use App\Domain\Student\StudentGradesView;

/**
 * حساب «الدروس التي أكمل بها» (المواد التي درجتها أقل من 50) لطالب،
 * وفق ترتيب مواد الاختصاص. مصدر واحد للمنطق يُستخدم في العرض والتثبيت.
 */
final class CompletedSubjectsCalculator
{
    private const PASS_THRESHOLD = 50;

    /**
     * @param  list<string>  $subjects  مواد الاختصاص بالترتيب
     * @return list<string> أسماء المواد الراسب بها (< 50)
     */
    public function calculate(?StudentGradesView $gradesView, array $subjects): array
    {
        $scoreBySubject = $this->buildScoreMap($gradesView);
        $completed = [];
        foreach ($subjects as $subjectName) {
            if ($this->scoreIntFor($subjectName, $scoreBySubject) < self::PASS_THRESHOLD) {
                $completed[] = $subjectName;
            }
        }

        return $completed;
    }

    private function scoreIntFor(string $subjectName, array $scoreBySubject): int
    {
        $score = trim($this->findScoreForSubject($subjectName, $scoreBySubject));

        return $score !== '' && is_numeric($score) ? (int) round((float) $score) : 0;
    }

    /**
     * @return array<string, string>
     */
    private function buildScoreMap(?StudentGradesView $gradesView): array
    {
        $map = [];
        if ($gradesView === null) {
            return $map;
        }
        foreach ($gradesView->grades as $row) {
            if (! is_array($row)) {
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

    /**
     * @param  array<string, string>  $scoreBySubject
     */
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
            if ($this->normalize((string) $dbSubject) === $key) {
                return $score;
            }
        }

        return '';
    }

    private function normalize(string $name): string
    {
        $t = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);

        return str_replace(['أ', 'إ', 'آ', 'ى'], 'ا', $t);
    }
}
