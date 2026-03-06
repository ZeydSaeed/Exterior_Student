<?php

namespace App\Http\Controllers;

use App\Application\Certificate\Query\GetCertificateSignatureEmployeesQueryHandler;
use App\Application\Student\Query\GetStudentCertificateQueryHandler;
use App\Application\Student\Query\GetStudentCertificateWithGradesQueryHandler;
use Illuminate\Http\Request;

/**
 * عرض صفحة التأييد (تأييد بدون درجات) — طبقة العرض فقط
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

        return view('students.certificate', compact('dto'));
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

        return view('students.certificate-with-grades', compact('dto'));
    }
}
