<?php

namespace App\Http\Controllers;

use App\Application\Student\Import\ImportStudentsFromExcelUseCase;
use App\Http\Requests\ImportStudentsExcelRequest;
use App\Support\StudentListFiltersSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StudentExcelImportController extends Controller
{
    public function __construct(
        private ImportStudentsFromExcelUseCase $importUseCase
    ) {}

    public function show(): View
    {
        return view('students.import-excel');
    }

    public function upload(ImportStudentsExcelRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $result = $this->importUseCase->uploadAndStage($file);
        if ($result['total'] === 0) {
            return redirect()
                ->route('students.import-excel')
                ->with('error', 'الملف فارغ أو لا يحتوي على صفوف بيانات.');
        }
        return redirect()
            ->route('students.import-excel.preview', ['batch_id' => $result['batch_id']])
            ->with('import_result', $result);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $batchId = $request->get('batch_id');
        if (! is_string($batchId) || $batchId === '') {
            return redirect()->route('students.import-excel')->with('error', 'معرف الدفعة غير صالح.');
        }
        $rows = $this->importUseCase->getPreview($batchId);
        if (empty($rows)) {
            return redirect()->route('students.import-excel')->with('error', 'لم يتم العثور على دفعة الاستيراد.');
        }
        $result = session('import_result', ['total' => count($rows), 'valid' => 0, 'failed' => 0]);
        return view('students.import-excel-preview', [
            'batchId' => $batchId,
            'rows' => $rows,
            'total' => $result['total'],
            'validCount' => $result['valid'],
            'failedCount' => $result['failed'],
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $batchId = $request->input('batch_id');
        if (! is_string($batchId) || $batchId === '') {
            return redirect()->route('students.import-excel')->with('error', 'معرف الدفعة غير صالح.');
        }
        $result = $this->importUseCase->processValidRows($batchId);
        $msg = "تم إدراج {$result['success']} طالب بنجاح.";
        if ($result['failed'] > 0) {
            $msg .= ' فشل ' . $result['failed'] . ' صف.';
        }
        if (! empty($result['errors'])) {
            $msg .= ' تفاصيل: ' . implode('؛ ', array_slice($result['errors'], 0, 3));
        }
        $request->session()->forget(StudentListFiltersSession::SESSION_KEY);
        return redirect()
            ->route('students.index')
            ->with('status', $msg);
    }
}
