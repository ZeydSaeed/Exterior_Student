<?php

namespace App\Http\Controllers;

use App\Application\Auth\Command\CreateAuthAccountCommandHandler;
use App\Application\Auth\Command\DeleteAuthAccountCommandHandler;
use App\Application\Auth\Command\UpdateAuthAccountCommandHandler;
use App\Application\Auth\Query\ListAuthAccountsQueryHandler;
use App\Http\Requests\StoreAuthAccountRequest;
use App\Http\Requests\UpdateAuthAccountRequest;
use App\Support\AppUserMessage;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAccountController extends Controller
{
    public function index(ListAuthAccountsQueryHandler $handler): View
    {
        $data = $handler->handle();

        return view('accounts.index', $data);
    }

    public function store(StoreAuthAccountRequest $request, CreateAuthAccountCommandHandler $handler): RedirectResponse
    {
        try {
            $handler->handle(
                name: (string) $request->validated('name'),
                username: (string) $request->validated('username'),
                password: (string) $request->validated('password'),
                isAdmin: (bool) $request->boolean('is_admin'),
                permissions: $request->validated('permissions') ?? [],
            );
        } catch (DomainException $e) {
            return back()
                ->withInput()
                ->with('app_dialog', AppUserMessage::fromText($e->getMessage(), AppUserMessage::TYPE_WARNING));
        }

        return redirect()->route('accounts.index')->with('success', 'تم إضافة الحساب بنجاح.');
    }

    public function update(int $id, UpdateAuthAccountRequest $request, UpdateAuthAccountCommandHandler $handler): RedirectResponse
    {
        $password = $request->validated('password');
        if ($password === null || trim((string) $password) === '') {
            $password = null;
        }

        try {
            $handler->handle(
                id: $id,
                name: (string) $request->validated('name'),
                username: (string) $request->validated('username'),
                password: $password,
                isAdmin: (bool) $request->boolean('is_admin'),
                permissions: $request->validated('permissions') ?? [],
            );
        } catch (DomainException $e) {
            return back()
                ->withInput()
                ->with('app_dialog', AppUserMessage::fromText($e->getMessage(), AppUserMessage::TYPE_WARNING));
        }

        return redirect()->route('accounts.index')->with('success', 'تم تحديث الحساب بنجاح.');
    }

    public function destroy(int $id, Request $request, DeleteAuthAccountCommandHandler $handler): RedirectResponse
    {
        try {
            $handler->handle($id, $request->user()?->id);
        } catch (DomainException $e) {
            return back()
                ->with('app_dialog', AppUserMessage::fromText($e->getMessage(), AppUserMessage::TYPE_WARNING));
        }

        return redirect()->route('accounts.index')->with('success', 'تم حذف الحساب.');
    }
}
