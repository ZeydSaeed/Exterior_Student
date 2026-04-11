<?php

namespace App\Http\Controllers;

use App\Application\Student\Query\ListRepeatersReportQuery;
use App\Application\Student\Query\ListRepeatersReportQueryHandler;
use Illuminate\View\View;

final class StudentRepeatersController extends Controller
{
    public function index(ListRepeatersReportQueryHandler $handler): View
    {
        $request = request();
        $year = trim((string) $request->query('year', ''));

        $query = ListRepeatersReportQuery::fromArray([
            'branch' => $request->query('branch'),
            'major' => $request->query('major'),
            'gender' => $request->query('gender'),
            'year' => $year !== '' ? $year : null,
            'search' => $request->query('search'),
        ]);

        $response = $handler->handle($query);

        return view('students.repeaters.index', array_merge($response->toArray(), [
            'yearRequiredError' => $year === '' ? 'يرجى تحديد العام الدراسي أولاً لعرض الطلبة المعيدين.' : null,
        ]));
    }
}
