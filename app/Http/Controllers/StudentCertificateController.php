<?php

namespace App\Http\Controllers;

use App\Application\Certificate\Query\GetCertificateSignatureEmployeesQueryHandler;
use App\Application\Student\Query\GetStudentCertificateQueryHandler;
use App\Application\Student\Query\GetStudentCertificateWithGradesQueryHandler;
use App\Domain\Attestation\Attestation;
use App\Domain\Attestation\AttestationQueryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * عرض صفحة التأييد — طبقة العرض فقط.
 * حقلَا العدد و "الى" يفتحان فارغين ويُدخلان يدوياً لكل تأييد؛ عند الطباعة تُقرأ قيمتهما وتُحفظ في قاعدة البيانات.
 * وضع التعديل (?attestation=id) يفتح تأييداً محفوظاً من السجل الشخصي للحفظ بالتحديث،
 * مع اعتماد موظفي التوقيع المختارين حالياً من صفحة الموظفين (مثل الإنشاء).
 */
final class StudentCertificateController
{
    public function __construct(
        private GetStudentCertificateQueryHandler $handler,
        private GetStudentCertificateWithGradesQueryHandler $handlerWithGrades,
        private GetCertificateSignatureEmployeesQueryHandler $signatureEmployeesHandler,
        private AttestationQueryRepository $attestationQuery,
    ) {}

    public function show(int $id, Request $request): View|RedirectResponse|JsonResponse
    {
        return $this->renderCertificate(
            studentId: $id,
            request: $request,
            viewName: 'students.certificate',
            clearFlash: true,
            handler: fn (array $employees) => $this->handler->handle($id, $employees),
        );
    }

    public function showWithGrades(int $id, Request $request): View|RedirectResponse|JsonResponse
    {
        return $this->renderCertificate(
            studentId: $id,
            request: $request,
            viewName: 'students.certificate-with-grades',
            clearFlash: false,
            handler: fn (array $employees) => $this->handlerWithGrades->handle($id, $employees),
        );
    }

    /**
     * @param  callable(array<int, array{type: string, name: string}>): object  $handler
     */
    private function renderCertificate(
        int $studentId,
        Request $request,
        string $viewName,
        bool $clearFlash,
        callable $handler,
    ): View|RedirectResponse|JsonResponse {
        $attestation = $this->resolveAttestationForEdit($studentId, $request);
        if ($attestation instanceof RedirectResponse) {
            return $attestation;
        }

        // الإنشاء والتعديل: اعتمِد الموظفون المختارين حالياً من صفحة الموظفين
        $employees = $this->signatureEmployeesHandler->handle();

        try {
            $dto = $handler($employees);
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

        if ($clearFlash) {
            $request->session()->forget(['app_dialog', 'flash_error', 'error', 'warning']);
        }

        return view($viewName, [
            'dto' => $dto,
            'studentId' => $studentId,
            'attestation' => $attestation,
            'isEdit' => $attestation !== null,
        ]);
    }

    private function resolveAttestationForEdit(int $studentId, Request $request): Attestation|RedirectResponse|null
    {
        $attestationId = (int) $request->query('attestation', 0);
        if ($attestationId < 1) {
            return null;
        }

        $attestation = $this->attestationQuery->findByStudentAndId($studentId, $attestationId);
        if ($attestation === null) {
            abort(404, 'لم يتم العثور على التأييد.');
        }

        $currentRoute = $request->route()?->getName();
        $targetRoute = $attestation->type === 'with_grades'
            ? 'students.certificate-with-grades'
            : 'students.certificate';

        if ($currentRoute !== null && $currentRoute !== $targetRoute) {
            return redirect()->route($targetRoute, [
                'id' => $studentId,
                'attestation' => $attestation->id,
            ]);
        }

        return $attestation;
    }
}
