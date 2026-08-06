<?php

namespace App\Http\Controllers;

use App\Application\Student\Import\ImportStudentResultsFromExcelUseCase;
use App\Http\Requests\ImportStudentResultsExcelRequest;
use App\Support\StudentListFiltersSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StudentResultsExcelImportController extends Controller
{
    public function __construct(
        private ImportStudentResultsFromExcelUseCase $useCase
    ) {}

    public function show(): View
    {
        return view('students.import-results-excel');
    }

    public function upload(ImportStudentResultsExcelRequest $request): RedirectResponse
    {
        $result = $this->useCase->uploadAndStage(
            $request->file('file'),
            (string) $request->validated('round')
        );
        if ($result['total'] === 0) {
            return redirect()->route('students.results-import-excel')->with('error', 'الملف فارغ أو لا يحتوي صفوفاً.');
        }

        return redirect()->route('students.results-import-excel.preview', ['batch_id' => $result['batch_id']])->with('import_result', $result);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $batchId = (string) $request->get('batch_id', '');
        if ($batchId === '') {
            return redirect()->route('students.results-import-excel')->with('error', 'معرف الدفعة غير صالح.');
        }
        $rows = $this->useCase->getPreview($batchId);
        if ($rows === []) {
            return redirect()->route('students.results-import-excel')->with('error', 'لا توجد دفعة مطابقة.');
        }
        $result = session('import_result', ['total' => count($rows), 'valid' => 0, 'failed' => 0]);
        $selectedRound = '';
        foreach ($rows as $row) {
            $round = trim((string) ($row->round ?? ''));
            if ($round !== '') {
                $selectedRound = $round;
                break;
            }
        }

        return view('students.import-results-excel-preview', [
            'batchId' => $batchId,
            'rows' => $rows,
            'total' => $result['total'],
            'validCount' => $result['valid'],
            'failedCount' => $result['failed'],
            'selectedRound' => $selectedRound,
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $batchId = (string) $request->input('batch_id', '');
        if ($batchId === '') {
            return redirect()->route('students.results-import-excel')->with('error', 'معرف الدفعة غير صالح.');
        }
        $result = $this->useCase->processValidRows($batchId);
        $msg = "تم ترحيل نتائج {$result['success']} طالب بنجاح.";
        if ($result['failed'] > 0) {
            $msg .= " فشل {$result['failed']} صف.";
        }
        if ($result['errors'] !== []) {
            $msg .= ' تفاصيل: '.implode('؛ ', array_slice($result['errors'], 0, 3));
        }
        $request->session()->forget(StudentListFiltersSession::SESSION_KEY);

        return redirect()->route('students.index')->with('status', $msg);
    }
}
