<?php

use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Support\AppExceptionPresenter;
use App\Support\AppReturnUrl;
use App\Support\AppUserMessage;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => EnsureUserHasPermission::class,
            'admin' => EnsureUserIsAdmin::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if ($e instanceof AuthenticationException) {
                return null;
            }

            if ($e instanceof ValidationException) {
                if ($request->expectsJson()
                    || $request->ajax()
                    || $request->wantsJson()
                    || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return null;
                }

                return redirect()
                    ->to(AppReturnUrl::for($request))
                    ->withInput()
                    ->with('app_dialog', AppUserMessage::fromLines(
                        $e->validator->errors()->all(),
                        AppUserMessage::TYPE_WARNING
                    ));
            }

            return AppExceptionPresenter::render($e, $request);
        });
    })->create();
