<?php

namespace App\Application\Student\Command;

use App\Application\Student\Query\ListStudentsQuery;
use App\Domain\Student\StudentCommandRepository;
use App\Domain\Student\StudentQueryRepository;
use Illuminate\Support\Facades\DB;

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
        $ids = $this->queryRepository->listIdsWithFilters($filtersForRepo);

        if ($ids === []) {
            return 0;
        }

        $deleted = 0;

        DB::transaction(function () use ($ids, &$deleted): void {
            foreach ($ids as $id) {
                $student = $this->queryRepository->getStudentById($id);
                if ($student === null) {
                    continue;
                }

                $result = $student->result !== null ? trim($student->result) : '';
                if ($result === '' || ($result !== 'راسب' && $result !== 'معيد')) {
                    continue;
                }

                // تطبيق قواعد الدومين نفسها المستخدمة في الحذف الفردي
                $student->ensureCanBeDeleted();

                if ($this->commandRepository->deleteStudent($id)) {
                    $deleted++;
                }
            }
        });

        return $deleted;
    }
}
