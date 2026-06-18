<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * يحدد عنوان إعادة التوجيه بعد خطأ ليبقى المستخدم على الصفحة التي كان يعمل عليها.
 */
final class AppReturnUrl
{
    public static function for(Request $request): string
    {
        if (! $request->isMethod('GET')) {
            $fallback = self::fallbackFromRoute($request);
            if ($fallback !== null) {
                return $fallback;
            }

            $referer = trim((string) $request->headers->get('referer', ''));
            if ($referer !== '' && self::isSameAppUrl($referer)) {
                return $referer;
            }
        } else {
            return $request->fullUrl();
        }

        $previous = url()->previous();
        if ($previous !== '' && self::isSameAppUrl($previous) && $previous !== $request->fullUrl()) {
            return $previous;
        }

        return route('dashboard');
    }

    private static function fallbackFromRoute(Request $request): ?string
    {
        $route = $request->route();
        if ($route === null) {
            return null;
        }

        $name = (string) $route->getName();
        $id = $route->parameter('id');

        return match ($name) {
            'students.documents.store',
            'students.documents.update',
            'students.documents.destroy' => route('students.documents.index', ['id' => $id]),

            'students.profile.attestations.store',
            'students.profile.attestations.update',
            'students.profile.attestations.destroy' => route('students.profile.show', ['id' => $id]),

            'students.grades.update' => route('students.grades', ['id' => $id]),

            'students.document.update' => route('students.document', ['id' => $id]),

            'students.store' => route('students.create'),
            'students.destroy' => StudentListFiltersSession::indexUrl($request),

            'employees.store',
            'employees.update',
            'employees.destroy',
            'employees.signatures.store' => route('employees.index'),

            'students.import-excel.upload' => route('students.import-excel'),
            'students.import-excel.process' => route('students.import-excel.preview'),
            'students.results-import-excel.upload' => route('students.results-import-excel'),
            'students.results-import-excel.process' => route('students.results-import-excel.preview'),

            'database-backup.store' => route('dashboard'),

            default => null,
        };
    }

    private static function isSameAppUrl(string $url): bool
    {
        return str_starts_with($url, url('/'));
    }
}
