<?php

namespace App\Http\Controllers;

use App\Application\Employee\Command\CreateEmployeeCommandHandler;
use App\Application\Employee\Command\DeleteEmployeeCommandHandler;
use App\Application\Employee\Command\UpdateEmployeeCommandHandler;
use App\Application\Employee\Query\ListEmployeesQueryHandler;
use App\Domain\Employee\EmployeeQueryRepository;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(ListEmployeesQueryHandler $handler)
    {
        $response = $handler->handle();

        return view('employees.index', $response->toArray());
    }

    public function store(StoreEmployeeRequest $request, CreateEmployeeCommandHandler $handler)
    {
        $handler->handle(
            type: (string) $request->validated('type'),
            name: (string) $request->validated('name')
        );

        return redirect()->back();
    }

    public function update(int $id, UpdateEmployeeRequest $request, UpdateEmployeeCommandHandler $handler)
    {
        $handler->handle(
            id: $id,
            type: (string) $request->validated('type'),
            name: (string) $request->validated('name')
        );

        return redirect()->back();
    }

    public function destroy(int $id, DeleteEmployeeCommandHandler $handler)
    {
        $handler->handle($id);

        return redirect()->back();
    }

    /**
     * حفظ الموظفين المختارين في الجلسة (لصفحة التأييد).
     */
    public function storeSelectedToSession(Request $request, EmployeeQueryRepository $queryRepository)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_slice(array_map('intval', array_filter($ids)), 0, 2);

        $all = $queryRepository->all();
        $byId = [];
        foreach ($all as $emp) {
            $byId[$emp->id] = ['type' => $emp->type, 'name' => $emp->name];
        }

        $selected = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $selected[] = $byId[$id];
            }
        }

        session(['selected_employees' => $selected]);

        return response()->json(['ok' => true]);
    }
}

