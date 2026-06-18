<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * يعرض الأخطاء للمستخدم عبر دايلوج عربي بدلاً من صفحة Laravel الافتراضية.
 */
final class AppExceptionPresenter
{
    public static function render(Throwable $e, Request $request): ?Response
    {
        if ($e instanceof ValidationException) {
            return null;
        }

        $dialog = AppUserMessage::fromThrowable($e);
        $status = self::statusCode($e);

        if (self::wantsJson($request)) {
            return response()->json([
                'error' => true,
                'type' => $dialog['type'],
                'title' => $dialog['title'],
                'message' => $dialog['message'],
            ], $status);
        }

        $returnUrl = AppReturnUrl::for($request);
        $redirect = redirect()->to($returnUrl)->with('app_dialog', $dialog);

        if (! $request->isMethod('GET')) {
            $redirect->withInput();
        }

        return $redirect;
    }

    private static function statusCode(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        if ($e instanceof \RuntimeException && $e->getCode() >= 400 && $e->getCode() < 600) {
            return $e->getCode();
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private static function wantsJson(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->wantsJson()
            || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }
}
