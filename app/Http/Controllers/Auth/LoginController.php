<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Command\LoginUserCommandHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request, LoginUserCommandHandler $handler): RedirectResponse
    {
        try {
            $userId = $handler->handle(
                username: (string) $request->validated('username'),
                password: (string) $request->validated('password'),
            );
        } catch (DomainException $e) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => $e->getMessage()]);
        }

        $user = User::query()->findOrFail($userId);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
