<?php

namespace App\Application\Student\Query;

use App\Application\Student\DTO\StudentDocumentPageDTO;
use App\Domain\Student\StudentQueryRepository;

/**
 * Use Case: جلب قيود (سجلات قيد) لجميع الطلاب المطابقة لفلاتر الفرع/الاختصاص/العام/الجنس للطباعة الدفعية.
 */
final class GetBulkStudentDocumentsPrintQueryHandler
{
    public function __construct(
        private StudentQueryRepository $studentRepository,
        private GetStudentDocumentPageQueryHandler $documentPageHandler,
    ) {}

    /**
     * @return list<StudentDocumentPageDTO>
     */
    public function handle(ListStudentsQuery $query): array
    {
        $ids = $this->studentRepository->listIdsWithFilters($query->filtersForRepository());
        $out = [];

        foreach ($ids as $id) {
            try {
                $out[] = $this->documentPageHandler->handle($id);
            } catch (\RuntimeException $e) {
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }

        return $out;
    }
}
