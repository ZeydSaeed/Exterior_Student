<?php

namespace App\Application\Student\Query;

use App\Application\Certificate\Query\GetCertificateSignatureEmployeesQueryHandler;
use App\Application\Student\DTO\StudentDocumentPageDTO;
use App\Domain\Student\StudentQueryRepository;

/**
 * Use Case: جلب قيود (سجلات قيد) لجميع الطلاب المطابقة لفلاتر الفرع/الاختصاص/العام/الجنس للطباعة الدفعية.
 */
final class GetBulkStudentDocumentsPrintQueryHandler
{
    public const WINDOW_BEFORE = 3;

    public const WINDOW_AFTER = 3;

    public function __construct(
        private StudentQueryRepository $studentRepository,
        private GetStudentDocumentPageQueryHandler $documentPageHandler,
        private GetCertificateSignatureEmployeesQueryHandler $signatureEmployeesHandler,
    ) {}

    /**
     * @return list<int>
     */
    public function listIds(ListStudentsQuery $query): array
    {
        return $this->studentRepository->listIdsWithFilters($query->filtersForRepository());
    }

    /**
     * @param  list<int>  $ids
     * @return list<StudentDocumentPageDTO>
     */
    public function handleForIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $employees = $this->signatureEmployeesHandler->handle();
        $out = [];

        foreach ($ids as $id) {
            try {
                $out[] = $this->documentPageHandler->handle($id, $employees);
            } catch (\RuntimeException $e) {
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }

        return $out;
    }

    /**
     * @param  list<int>  $ids
     * @return array{start: int, end: int, ids: list<int>}
     */
    public static function initialWindow(array $ids, ?int $focusId = null): array
    {
        if ($ids === []) {
            return ['start' => 0, 'end' => -1, 'ids' => []];
        }

        $focusIndex = 0;
        if ($focusId !== null && $focusId > 0) {
            $found = array_search($focusId, $ids, true);
            if ($found !== false) {
                $focusIndex = (int) $found;
            }
        }

        $start = max(0, $focusIndex - self::WINDOW_BEFORE);
        $end = min(count($ids) - 1, $focusIndex + self::WINDOW_AFTER);

        return [
            'start' => $start,
            'end' => $end,
            'ids' => array_slice($ids, $start, $end - $start + 1),
        ];
    }

    /**
     * @return list<StudentDocumentPageDTO>
     */
    public function handle(ListStudentsQuery $query): array
    {
        return $this->handleForIds($this->listIds($query));
    }
}
