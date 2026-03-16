<?php

namespace App\Http\Controllers;

use App\Application\Student\Query\GetBulkStudentDocumentsPrintQueryHandler;
use App\Application\Student\Query\ListStudentsQuery;
use App\Application\Student\Query\ListStudentsQueryHandler;
use App\Support\StudentListFiltersSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * صفحة القيود (نفس تنسيق صفحة الموظفين) — معاينة وطباعة حسب الفلاتر.
 */
final class StudentDocumentsBulkPrintController extends Controller
{
    public function __construct(
        private GetBulkStudentDocumentsPrintQueryHandler $handler,
        private ListStudentsQueryHandler $listHandler,
    ) {}

    public function __invoke(Request $request): View
    {
        $merged = StudentListFiltersSession::mergeRequestWithSession($request);
        $normalized = StudentListFiltersSession::normalizeForQuery($merged);

        $branch = isset($merged['branch']) ? trim((string) $merged['branch']) : '';
        $major = isset($merged['major']) ? trim((string) $merged['major']) : '';
        $year = isset($merged['year']) ? trim((string) $merged['year']) : '';
        $filtersRequired = ($branch === '' || $major === '' || $year === '');

        $query = ListStudentsQuery::fromArray(array_merge(
            ['branch' => null, 'major' => null, 'gender' => null, 'year' => null, 'search' => null],
            $normalized
        ));

        $listResponse = $this->listHandler->handle($query);

        if ($filtersRequired) {
            return view('students.documents-bulk-print', [
                'dtos' => [],
                'filtersRequired' => true,
                'branches' => $listResponse->branches,
                'majors' => $listResponse->majors,
                'genders' => $listResponse->genders,
                'academicYears' => $listResponse->academicYears,
            ]);
        }

        $dtos = $this->handler->handle($query);

        return view('students.documents-bulk-print', [
            'dtos' => $dtos,
            'filtersRequired' => false,
            'branches' => $listResponse->branches,
            'majors' => $listResponse->majors,
            'genders' => $listResponse->genders,
            'academicYears' => $listResponse->academicYears,
        ]);
    }
}
