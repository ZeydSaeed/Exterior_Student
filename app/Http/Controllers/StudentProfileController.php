<?php

namespace App\Http\Controllers;

use App\Application\Profile\Command\CreateAttestationCommandHandler;
use App\Application\Profile\Command\DeleteAttestationCommandHandler;
use App\Application\Profile\Command\UpdateAttestationCommandHandler;
use App\Application\Profile\Query\GetStudentProfileQueryHandler;
use App\Http\Requests\StoreAttestationRequest;
use App\Http\Requests\UpdateAttestationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * السجل الشخصي للطالب = التأييدات + الوثائق.
 */
final class StudentProfileController
{
    public function __construct(
        private GetStudentProfileQueryHandler $profileQueryHandler,
        private CreateAttestationCommandHandler $createAttestationHandler,
        private UpdateAttestationCommandHandler $updateAttestationHandler,
        private DeleteAttestationCommandHandler $deleteAttestationHandler
    ) {}

    public function show(int $id): View|RedirectResponse
    {
        $dto = $this->profileQueryHandler->handle($id);

        if ($dto === null) {
            abort(404, 'لم يتم العثور على الطالب.');
        }

        return view('students.profile', compact('dto'));
    }

    public function storeAttestation(int $id, StoreAttestationRequest $request): RedirectResponse
    {
        $dto = $this->profileQueryHandler->handle($id);
        if ($dto === null) {
            abort(404, 'لم يتم العثور على الطالب.');
        }

        $examNumber = $dto->examNumber ?? '';
        if ($examNumber === '') {
            abort(404, 'الرقم الامتحاني غير متوفر.');
        }

        $validated = $request->validated();
        $this->createAttestationHandler->handle(
            examNumber: (string) $examNumber,
            type: (string) $validated['type'],
            date: isset($validated['date']) && $validated['date'] !== '' ? (string) $validated['date'] : null,
            number: isset($validated['number']) && $validated['number'] !== '' && $validated['number'] !== null ? trim((string) $validated['number']) : null,
            issuedTo: isset($validated['issued_to']) && $validated['issued_to'] !== '' && $validated['issued_to'] !== null ? trim((string) $validated['issued_to']) : null,
            rightTitle: isset($validated['right_title']) && $validated['right_title'] !== '' ? trim((string) $validated['right_title']) : null,
            rightEmployeeName: isset($validated['right_employee_name']) && $validated['right_employee_name'] !== '' ? trim((string) $validated['right_employee_name']) : null,
            leftTitle: isset($validated['left_title']) && $validated['left_title'] !== '' ? trim((string) $validated['left_title']) : null,
            leftEmployeeName: isset($validated['left_employee_name']) && $validated['left_employee_name'] !== '' ? trim((string) $validated['left_employee_name']) : null,
        );

        return redirect()->route('students.profile.show', ['id' => $id]);
    }

    public function updateAttestation(int $id, int $attestationId, UpdateAttestationRequest $request): RedirectResponse
    {
        if ($this->profileQueryHandler->handle($id) === null) {
            abort(404, 'لم يتم العثور على الطالب.');
        }

        $this->updateAttestationHandler->handle(
            id: $attestationId,
            date: $request->validated('date') ? (string) $request->validated('date') : null,
            number: $request->validated('number'),
            issuedTo: $request->validated('issued_to'),
            rightTitle: $request->validated('right_title'),
            rightEmployeeName: $request->validated('right_employee_name'),
            leftTitle: $request->validated('left_title'),
            leftEmployeeName: $request->validated('left_employee_name'),
        );

        return redirect()->route('students.profile.show', ['id' => $id]);
    }

    public function destroyAttestation(int $id, int $attestationId): RedirectResponse
    {
        $this->deleteAttestationHandler->handle($attestationId);

        return redirect()->route('students.profile.show', ['id' => $id]);
    }
}
