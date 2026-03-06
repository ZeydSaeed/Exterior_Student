<?php

namespace App\Http\Controllers;

use App\Application\Certificate\Command\SaveCertificateSignatureSettingsCommandHandler;
use App\Application\Certificate\Query\GetCertificateSignatureSettingsQueryHandler;
use App\Application\Employee\Command\CreateEmployeeCommandHandler;
use App\Application\Employee\Command\DeleteEmployeeCommandHandler;
use App\Application\Employee\Command\UpdateEmployeeCommandHandler;
use App\Application\Employee\Query\ListEmployeesQueryHandler;
use App\Http\Requests\SaveCertificateSignatureSettingsRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    public function index(
        ListEmployeesQueryHandler $listHandler,
        GetCertificateSignatureSettingsQueryHandler $signatureSettingsHandler
    ) {
        $response = $listHandler->handle();
        $data = $response->toArray();

        $signatures = $signatureSettingsHandler->handle();
        $data['right_selected'] = $signatures['right'];
        $data['left_selected'] = $signatures['left'];

        return view('employees.index', $data);
    }

    public function store(StoreEmployeeRequest $request, CreateEmployeeCommandHandler $handler, SaveCertificateSignatureSettingsCommandHandler $signatureHandler)
    {
        $handler->handle(
            type: (string) $request->validated('type'),
            name: (string) $request->validated('name'),
            tableGroup: (int) $request->validated('table_group')
        );

        $this->saveSignaturesFromRequest($request, $signatureHandler);

        return redirect()->back();
    }

    public function update(int $id, UpdateEmployeeRequest $request, UpdateEmployeeCommandHandler $handler, SaveCertificateSignatureSettingsCommandHandler $signatureHandler)
    {
        $handler->handle(
            id: $id,
            type: (string) $request->validated('type'),
            name: (string) $request->validated('name')
        );

        $this->saveSignaturesFromRequest($request, $signatureHandler);

        return redirect()->back();
    }

    public function destroy(int $id, \Illuminate\Http\Request $request, DeleteEmployeeCommandHandler $handler, SaveCertificateSignatureSettingsCommandHandler $signatureHandler)
    {
        $handler->handle($id);

        $this->saveSignaturesFromRequest($request, $signatureHandler);

        return redirect()->back();
    }

    /**
     * حفظ إعدادات التواقيع من بيانات الطلب (إن وُجدت).
     */
    private function saveSignaturesFromRequest(\Illuminate\Http\Request $request, SaveCertificateSignatureSettingsCommandHandler $signatureHandler): void
    {
        $rightId = $request->input('right_signature');
        $leftId = $request->input('left_signature');
        if ($rightId === '' || $rightId === null) {
            $rightId = null;
        }
        if ($leftId === '' || $leftId === null) {
            $leftId = null;
        }
        if ($rightId === null && $leftId === null) {
            return;
        }
        $valid = $request->validate([
            'right_signature' => ['nullable', 'integer', 'exists:employees,id'],
            'left_signature' => ['nullable', 'integer', 'exists:employees,id'],
        ]);
        $signatureHandler->handle(
            rightEmployeeId: isset($valid['right_signature']) ? (int) $valid['right_signature'] : null,
            leftEmployeeId: isset($valid['left_signature']) ? (int) $valid['left_signature'] : null
        );
    }

    /**
     * حفظ إعدادات تواقيع التأييد (يمين / يسار) في قاعدة البيانات.
     */
    public function storeSignatures(
        SaveCertificateSignatureSettingsRequest $request,
        SaveCertificateSignatureSettingsCommandHandler $handler
    ) {
        $rightId = $request->validated('right_signature');
        $leftId = $request->validated('left_signature');

        $handler->handle(
            rightEmployeeId: $rightId !== null && $rightId !== '' ? (int) $rightId : null,
            leftEmployeeId: $leftId !== null && $leftId !== '' ? (int) $leftId : null
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back()->with('success', 'تم حفظ إعدادات التواقيع.');
    }
}
