<?php

namespace App\Http\Controllers;

use App\Application\Record\Command\CreateRecordCommandHandler;
use App\Application\Record\Command\DeleteRecordCommandHandler;
use App\Application\Record\Command\UpdateRecordCommandHandler;
use App\Application\Record\Query\GetStudentDocumentsPageQueryHandler;
use App\Http\Requests\StoreRecordRequest;
use App\Http\Requests\UpdateRecordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * عرض وإدارة وثائق الطالب — طبقة العرض فقط (داخل الداشبورد).
 */
final class StudentRecordsController
{
    public function __construct(
        private GetStudentDocumentsPageQueryHandler $queryHandler,
        private CreateRecordCommandHandler $commandHandler,
        private UpdateRecordCommandHandler $updateCommandHandler,
        private DeleteRecordCommandHandler $deleteCommandHandler
    ) {}

    public function index(int $id): View|RedirectResponse
    {
        $dto = $this->queryHandler->handle($id);

        if ($dto === null) {
            abort(404, 'لم يتم العثور على الطالب.');
        }

        return view('student-records.index', compact('dto'));
    }

    public function store(int $id, StoreRecordRequest $request): RedirectResponse
    {
        $dto = $this->queryHandler->handle($id);

        if ($dto === null) {
            abort(404, 'لم يتم العثور على الطالب.');
        }

        $this->commandHandler->handle(
            studentId: $id,
            documentNumber: $request->validated('document_number'),
            documentDate: $request->validated('document_date') ? (string) $request->validated('document_date') : null,
            addressee: $request->validated('addressee'),
            purpose: $request->validated('purpose'),
        );

        return redirect()
            ->route('students.documents.index', ['id' => $id]);
    }

    public function update(int $id, int $recordId, UpdateRecordRequest $request): RedirectResponse
    {
        if ($this->queryHandler->handle($id) === null) {
            abort(404, 'لم يتم العثور على الطالب.');
        }

        $this->updateCommandHandler->handle(
            recordId: $recordId,
            documentNumber: $request->validated('document_number'),
            documentDate: $request->validated('document_date') ? (string) $request->validated('document_date') : null,
            addressee: $request->validated('addressee'),
            purpose: $request->validated('purpose'),
        );

        return redirect()
            ->route('students.documents.index', ['id' => $id]);
    }

    public function destroy(int $id, int $recordId): RedirectResponse
    {
        $this->deleteCommandHandler->handle($recordId);

        return redirect()
            ->route('students.documents.index', ['id' => $id]);
    }
}
