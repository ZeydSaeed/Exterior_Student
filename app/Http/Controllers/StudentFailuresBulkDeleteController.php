<?php

namespace App\Http\Controllers;

use App\Application\Student\Command\DeleteFailedStudentsByFiltersCommandHandler;
use App\Application\Student\Query\ListStudentsQuery;
use App\Support\StudentListFiltersSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * حذف الطلبة الراسبين/المعيدين بحسب فلاتر السايد بار — طبقة العرض فقط.
 */
final class StudentFailuresBulkDeleteController extends Controller
{
    public function __construct(
        private DeleteFailedStudentsByFiltersCommandHandler $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        // نفس منطق ثبات الفلاتر: ندمج مع الجلسة، لكن نحترم ما أرسله المستخدم صراحة
        $merged = StudentListFiltersSession::mergeRequestWithSession($request);
        $normalized = StudentListFiltersSession::normalizeForQuery($merged);

        $query = ListStudentsQuery::fromArray([
            'branch' => $normalized['branch'] ?? null,
            'major' => $normalized['major'] ?? null,
            'gender' => $normalized['gender'] ?? null,
            'year' => $normalized['year'] ?? null,
            'round' => $normalized['round'] ?? null,
            'search' => null, // الحذف يعتمد على الفلاتر الأساسية فقط
        ]);

        $count = $this->handler->handle($query);

        return response()->json([
            'deleted' => $count,
        ]);
    }
}
