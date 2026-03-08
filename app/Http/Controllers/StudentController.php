<?php

namespace App\Http\Controllers;

use App\Application\Student\Command\CreateStudentCommandHandler;
use App\Application\Student\Command\DeleteStudentCommandHandler;
use App\Application\Student\Query\GetCreateStudentFormQueryHandler;
use App\Application\Student\Query\GetStudentGradesQueryHandler;
use App\Application\Student\Query\ListStudentsQuery;
use App\Application\Student\Query\ListStudentsQueryHandler;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentGradesRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(ListStudentsQueryHandler $handler)
    {
        $query = ListStudentsQuery::fromArray(
            request()->only(['branch', 'major', 'gender', 'year', 'search'])
        );

        $response = $handler->handle($query);

        return view('students.index', array_merge($response->toArray(), [
            'flash_error'   => session('error'),
            'flash_status'  => session('status'),
        ]));
    }

    public function create(GetCreateStudentFormQueryHandler $formHandler): View
    {
        $form = $formHandler->handle();
        return view('students.create', $form);
    }

    public function store(StoreStudentRequest $request, CreateStudentCommandHandler $handler): RedirectResponse
    {
        $id = $handler->handle($request->dataForCreate());
        return redirect()
            ->route('students.index')
            ->with('status', 'تمت إضافة الطالب بنجاح.');
    }

    public function grades(int $id, GetStudentGradesQueryHandler $handler)
    {
        $dto = $handler->handle($id);

        if ($dto === null) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json($dto->toArray());
    }

    public function updateGrades(int $id, UpdateStudentGradesRequest $request, UpdateStudentGradesCommandHandler $handler)
    {
        try {
            $payload = $request->normalizedPayload();
            $ok = $handler->handle($id, $payload);
            if (!$ok) {
                return response()->json(['error' => 'not_found'], 404);
            }
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'server_error',
                'message' => config('app.debug') ? $e->getMessage() : 'تعذر حفظ التعديلات.',
            ], 500);
        }
    }

    public function destroy(int $id, DeleteStudentCommandHandler $handler)
    {
        try {
            $ok = $handler->handle($id);
            if (!$ok) {
                return redirect()
                    ->route('students.index')
                    ->with('error', 'لم يتم العثور على الطالب أو تعذر حذفه.');
            }

            return redirect()
                ->route('students.index')
                ->with('status', 'تم حذف الطالب بنجاح.');
        } catch (\DomainException $e) {
            return redirect()
                ->route('students.index')
                ->with('error', $e->getMessage());
        }
    }
}
