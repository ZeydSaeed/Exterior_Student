<?php

namespace App\Application\Student\Command;

use App\Application\Student\Query\ListStudentsQuery;
use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;

/**
 * أمر حذف جميع الطلبة الراسبين/المعيدين بحسب فلاتر الفرع/الاختصاص/الجنس/العام الدراسي.
 */
final class DeleteFailedStudentsByFiltersCommandHandler
{
    public function __construct(
        private StudentQueryRepository $queryRepository,
        private StudentCommandRepository $commandRepository,
    ) {}

    /**
     * @return int عدد الطلبة الذين تم حذفهم فعلياً
     */
    public function handle(ListStudentsQuery $query): int
    {
        $filtersForRepo = $query->filtersForRepository();
        $failedIds = $this->queryRepository->listFailedIdsWithFilters($filtersForRepo);

        if ($failedIds === []) {
            return 0;
        }

        $idsToDelete = [];
        foreach ($failedIds as $id) {
            $student = $this->queryRepository->getStudentById($id);
            if ($student === null) {
                continue;
            }
            $student->ensureCanBeDeleted();
            $idsToDelete[] = $id;
        }

        if ($idsToDelete === []) {
            return 0;
        }

        return $this->commandRepository->deleteStudentsByIds($idsToDelete);
    }
}
