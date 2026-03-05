<?php

namespace App\Application\Student\Command;

use App\Domain\Student\StudentCommandRepository;

/**
 * معالج أمر تحديث بيانات الطالب ودرجاته (حفظ كامل الفورم من المودال)
 */
final class UpdateStudentGradesCommandHandler
{
    public function __construct(
        private StudentCommandRepository $commandRepository,
    ) {}

    /**
     * @param array{
     *   name_student?: string,
     *   name_father?: string,
     *   name_grandfather?: string,
     *   name_surname?: string,
     *   exam_number?: string,
     *   gender?: string,
     *   branch?: string,
     *   major?: string,
     *   academic_year?: string,
     *   result?: string,
     *   total?: string,
     *   average?: string,
     *   round?: string,
     *   grades?: array<int, array{subject?: string, score?: string}>
     * } $payload
     */
    public function handle(int $id, array $payload): bool
    {
        $normalized = $this->normalizePayload($payload);
        return $this->commandRepository->updateGrades($id, $normalized);
    }

    private function normalizePayload(array $payload): array
    {
        $out = [];
        $basic = ['name_student', 'name_father', 'name_grandfather', 'name_surname', 'exam_number', 'gender', 'branch', 'major', 'academic_year', 'result', 'total', 'average', 'round'];
        foreach ($basic as $key) {
            if (array_key_exists($key, $payload)) {
                $out[$key] = trim((string) $payload[$key]);
            }
        }
        $grades = $payload['grades'] ?? [];
        if (is_array($grades)) {
            $out['grades'] = [];
            foreach ($grades as $i => $row) {
                if (!is_array($row)) {
                    continue;
                }
                $out['grades'][$i] = [
                    'subject' => trim((string) ($row['subject'] ?? '')),
                    'score' => trim((string) ($row['score'] ?? '')),
                ];
            }
        }
        return $out;
    }
}
