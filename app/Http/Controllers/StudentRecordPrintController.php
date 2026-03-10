<?php

namespace App\Http\Controllers;

use App\Application\Student\Query\GetStudentDocumentPageQueryHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * عرض صفحة سجل قيد الطالب (قيد الطالب) — طبقة العرض فقط.
 * يعتمد على Use Case واحد يجلب كل البيانات (معلومات + درجات + وثائق + تواقيع).
 */
final class StudentRecordPrintController
{
    public function __construct(
        private GetStudentDocumentPageQueryHandler $documentPageHandler,
    ) {}

    public function show(int $id, Request $request): View|JsonResponse
    {
        try {
            $dto = $this->documentPageHandler->handle($id);
        } catch (\RuntimeException $e) {
            if ($e->getCode() === 404) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'not_found'], 404);
                }
                abort(404, 'لم يتم العثور على الطالب.');
            }
            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'student_id' => $dto->studentId,
                'exam_number' => $dto->examNumber,
                'full_name' => $dto->fullName,
                'grades_table' => $dto->gradesTable,
                'total' => $dto->total,
                'documents' => array_map(static fn ($r) => $r->toArray(), $dto->documents),
            ]);
        }

        return view('students.document', ['dto' => $dto]);
    }
}

