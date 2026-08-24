<?php

namespace App\Application\Student\Service;

use App\Domain\Student\SubjectCatalogInterface;

/**
 * إذا كانت درجات جميع مواد الفرع/الاختصاص مساوية لـ «حجب» فالنتيجة يجب أن تكون «حجب».
 */
final class AllBlockedGradesResultResolver
{
    public const BLOCKED_SCORE = 'حجب';

    public const BLOCKED_RESULT = 'حجب';

    public function __construct(
        private SubjectCatalogInterface $subjectCatalog,
    ) {}

    /**
     * @param  array<int, array{subject?: string, score?: string|int|float|null}>  $grades
     */
    public function shouldForceBlockedResult(string $branch, string $major, array $grades): bool
    {
        $subjects = $this->subjectCatalog->getSubjectsFor($branch, $major);
        if ($subjects === []) {
            return false;
        }

        $scoreBySubject = $this->buildScoreMap($grades);
        foreach ($subjects as $subjectName) {
            $score = $this->findScoreForSubject((string) $subjectName, $scoreBySubject);
            if ($score !== self::BLOCKED_SCORE) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function applyToPayload(array $payload): array
    {
        $branch = trim((string) ($payload['branch'] ?? ''));
        $major = trim((string) ($payload['major'] ?? ''));
        $grades = $payload['grades'] ?? [];
        if (! is_array($grades)) {
            return $payload;
        }

        if ($this->shouldForceBlockedResult($branch, $major, $grades)) {
            $payload['result'] = self::BLOCKED_RESULT;
        }

        return $payload;
    }

    /**
     * @param  array<int, array{subject?: string, score?: string|int|float|null}>  $grades
     * @return array<string, string>
     */
    private function buildScoreMap(array $grades): array
    {
        $map = [];
        foreach ($grades as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = $this->normalizeSubjectName((string) ($row['subject'] ?? ''));
            if ($name === '') {
                continue;
            }
            $map[$name] = trim((string) ($row['score'] ?? ''));
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $scoreBySubject
     */
    private function findScoreForSubject(string $catalogSubject, array $scoreBySubject): string
    {
        $key = $this->normalizeSubjectName($catalogSubject);
        if ($key === '') {
            return '';
        }

        return $scoreBySubject[$key] ?? '';
    }

    private function normalizeSubjectName(string $name): string
    {
        $t = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        $t = str_replace(['أ', 'إ', 'آ', 'ى'], 'ا', $t);

        return $t;
    }
}
