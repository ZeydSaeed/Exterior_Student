<?php

namespace App\Http\Controllers;

use App\Application\Certificate\Query\GetCertificateSignatureEmployeesQueryHandler;
use App\Application\Student\Query\GetStudentCertificateQueryHandler;
use App\Application\Student\Query\GetStudentCertificateWithGradesQueryHandler;
use Illuminate\Http\Request;

/**
 * عرض صفحة التأييد — طبقة العرض فقط.
 * حقلَا العدد و "الى" يفتحان فارغين ويُدخلان يدوياً لكل تأييد؛ عند الطباعة تُقرأ قيمتهما وتُحفظ في قاعدة البيانات.
 */
final class StudentCertificateController
{
    public function __construct(
        private GetStudentCertificateQueryHandler $handler,
        private GetStudentCertificateWithGradesQueryHandler $handlerWithGrades,
        private GetCertificateSignatureEmployeesQueryHandler $signatureEmployeesHandler
    ) {}

    public function show(int $id, Request $request)
    {
        $employees = $this->signatureEmployeesHandler->handle();

        try {
            $dto = $this->handler->handle($id, $employees);
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
            return response()->json($dto->toArray());
        }

        return view('students.certificate', ['dto' => $dto, 'studentId' => $id]);
    }

    public function showWithGrades(int $id, Request $request)
    {
        $employees = $this->signatureEmployeesHandler->handle();

        try {
            $dto = $this->handlerWithGrades->handle($id, $employees);
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
            return response()->json($dto->toArray());
        }

        return view('students.certificate-with-grades', ['dto' => $dto, 'studentId' => $id]);
    }
}
