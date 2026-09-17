<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * يثبت معرّف الحاسبة في كوكي المتصفح حتى تبقى إعدادات الموظفين محلية لكل جهاز.
 */
class AssignWorkstationId
{
    public const COOKIE = 'workstation_id';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = (string) $request->cookies->get(self::COOKIE, '');
        if (! self::isValid($id)) {
            $id = (string) Str::uuid();
        }
        $id = strtolower($id);
        $request->attributes->set(self::COOKIE, $id);

        $response = $next($request);

        $response->headers->setCookie(cookie(
            name: self::COOKIE,
            value: $id,
            minutes: 60 * 24 * 365 * 5,
            path: '/',
            domain: null,
            secure: false,
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        ));

        return $response;
    }

    public static function isValid(string $id): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $id);
    }
}
