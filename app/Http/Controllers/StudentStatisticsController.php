<?php

namespace App\Http\Controllers;

use App\Application\Student\Query\ListStudentStatisticsQuery;
use App\Application\Student\Query\ListStudentStatisticsQueryHandler;
use App\Support\StudentListFiltersSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class StudentStatisticsController extends Controller
{
    public function index(ListStudentStatisticsQueryHandler $handler): View|RedirectResponse
    {
        $request = request();
        $merged = StudentListFiltersSession::mergeRequestWithSession($request);

        if (StudentListFiltersSession::shouldRedirectToNormalize($request, $merged)) {
            StudentListFiltersSession::persist($request, $merged);

            return redirect()->to(StudentListFiltersSession::statisticsUrl($request, $merged));
        }

        $normalized = StudentListFiltersSession::normalizeForQuery($merged);
        $query = ListStudentStatisticsQuery::fromArray(array_merge(
            ['branch' => null, 'major' => null, 'gender' => null, 'year' => null, 'round' => null, 'result' => null, 'search' => null],
            $normalized
        ));

        $response = $handler->handle($query);
        StudentListFiltersSession::persist($request, $merged);

        return view('students.statistics.index', $response->toArray());
    }
}
