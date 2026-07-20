<?php

namespace App\Application\Student\Service;

use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;
use App\Domain\Student\SubjectCatalogInterface;

/**
 * تثبيت «الدروس التي أكمل بها» طالما الطالب في الدور الأول.
 *
 * تُعاد الكتابة عند كل حفظ درجات في الدور الأول (للسماح بالتصحيح)،
 * وتُجمَّد تلقائياً بمجرد انتقال الطالب لدور لاحق لأن الحفظ لا يلمسها حينها.
 */
final class FirstRoundSubjectsLocker
{
    private const FIRST_ROUND = 'الاول';

    public function __construct(
        private StudentQueryRepository $queryRepository,
        private StudentCommandRepository $commandRepository,
        private SubjectCatalogInterface $subjectCatalog,
        private CompletedSubjectsCalculator $completedSubjectsCalculator,
    ) {}

    public function lock(int $studentId): void
    {
        $info = $this->queryRepository->getStudentDocumentInfo($studentId);
        if ($info === null) {
            return;
        }
        if ($this->normalizeRound($info->round) !== self::FIRST_ROUND) {
            return;
        }

        $subjects = $this->subjectCatalog->getSubjectsFor($info->branch, $info->specialization);
        $gradesView = $this->queryRepository->getGradesById($studentId);
        $completed = $this->completedSubjectsCalculator->calculate($gradesView, $subjects);

        $this->commandRepository->saveLockedSubjectsCompleted($studentId, $completed);
    }

    private function normalizeRound(string $round): string
    {
        $round = trim(preg_replace('/\s+/u', ' ', $round) ?? $round);

        return str_replace(['أ', 'إ', 'آ', 'ى'], 'ا', $round);
    }
}
