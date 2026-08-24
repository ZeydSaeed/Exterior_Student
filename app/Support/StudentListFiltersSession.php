<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * يحافظ على فلاتر قائمة الطلاب (فرع، اختصاص، جنس، عام، دور، نتيجة) ورقم الصفحة في الجلسة
 * حتى يبقى الاختيار ثابتاً عند التنقل وإعادة فتح الصفحات.
 */
final class StudentListFiltersSession
{
    public const SESSION_KEY = 'students_list_filters';

    private const PAGE_KEY = 'page';

    /** @var list<string> */
    private const FILTER_KEYS = ['branch', 'major', 'gender', 'year', 'round', 'result'];

    /**
     * يدمج الطلب الحالي مع ما خُزّن في الجلسة لنفس المفاتيح.
     *
     * @return array<string, string>
     */
    public static function mergeRequestWithSession(Request $request): array
    {
        $stored = $request->session()->get(self::SESSION_KEY, []);
        $merged = [];

        foreach (self::FILTER_KEYS as $key) {
            if ($request->query->has($key)) {
                /* يشمل branch= فارغاً ليعني صراحةً «الكل» ويمسح الجلسة لهذا المفتاح */
                $merged[$key] = (string) $request->query->get($key);
            } elseif (isset($stored[$key]) && $stored[$key] !== '' && $stored[$key] !== null) {
                $merged[$key] = (string) $stored[$key];
            }
        }

        if ($request->query->has('search')) {
            $merged['search'] = (string) $request->query->get('search');
        } elseif (! empty($stored['search'])) {
            $merged['search'] = (string) $stored['search'];
        }

        if ($request->query->has(self::PAGE_KEY)) {
            $page = max(1, (int) $request->query->get(self::PAGE_KEY));
            if ($page > 1) {
                $merged[self::PAGE_KEY] = (string) $page;
            }
        } elseif (! self::filterOrSearchSubmitted($request) && ! empty($stored[self::PAGE_KEY])) {
            $merged[self::PAGE_KEY] = (string) $stored[self::PAGE_KEY];
        }

        return $merged;
    }

    /**
     * هل يجب إعادة التوجيه لمواءمة الرابط مع الجلسة؟
     */
    public static function shouldRedirectToNormalize(Request $request, array $merged): bool
    {
        if ($merged === []) {
            return false;
        }

        foreach (self::FILTER_KEYS as $key) {
            if (! isset($merged[$key]) || $merged[$key] === '') {
                continue;
            }
            $current = $request->query->get($key);
            if ($current === null || (string) $current !== (string) $merged[$key]) {
                return true;
            }
        }

        if (isset($merged['search']) && $merged['search'] !== '' && ! $request->query->has('search')) {
            return true;
        }

        if (isset($merged[self::PAGE_KEY]) && (int) $merged[self::PAGE_KEY] > 1) {
            $currentPage = $request->query->get(self::PAGE_KEY);
            if ($currentPage === null || (string) $currentPage !== (string) $merged[self::PAGE_KEY]) {
                return true;
            }
        }

        return false;
    }

    /**
     * حفظ الحالة بعد التطبيق: القيم غير الفارغة تُخزَّن؛ اختيار «الكل» (قيمة فارغة) يزيل المفتاح من الجلسة.
     *
     * @param  array<string, mixed>  $filters
     */
    public static function persist(Request $request, array $filters): void
    {
        $stored = $request->session()->get(self::SESSION_KEY, []);

        foreach (self::FILTER_KEYS as $key) {
            if (array_key_exists($key, $filters) && ($filters[$key] === '' || $filters[$key] === null)) {
                unset($stored[$key]);
            } elseif (isset($filters[$key]) && $filters[$key] !== '' && $filters[$key] !== null) {
                $value = (string) $filters[$key];
                if ($key === 'result') {
                    $value = ResultFilterVariants::resolveFilterOption($value);
                }
                $stored[$key] = $value;
            }
        }

        if (array_key_exists('search', $filters) && ($filters['search'] === '' || $filters['search'] === null)) {
            unset($stored['search']);
        } elseif (isset($filters['search']) && $filters['search'] !== '' && $filters['search'] !== null) {
            $stored['search'] = (string) $filters['search'];
        }

        if ($request->query->has(self::PAGE_KEY)) {
            $page = max(1, (int) $request->query->get(self::PAGE_KEY));
            if ($page > 1) {
                $stored[self::PAGE_KEY] = (string) $page;
            } else {
                unset($stored[self::PAGE_KEY]);
            }
        } elseif (self::filterOrSearchSubmitted($request)) {
            unset($stored[self::PAGE_KEY]);
        }

        $request->session()->put(self::SESSION_KEY, $stored);
    }

    /**
     * تحويل القيم الفارغة إلى null لـ ListStudentsQuery والريبو.
     *
     * @param  array<string, string>  $merged
     * @return array<string, string|null>
     */
    public static function normalizeForQuery(array $merged): array
    {
        $out = [];
        foreach (self::FILTER_KEYS as $key) {
            if (! array_key_exists($key, $merged)) {
                continue;
            }
            $v = $merged[$key];
            if ($key === 'result' && $v !== '' && $v !== null) {
                $v = ResultFilterVariants::resolveFilterOption((string) $v);
            }
            $out[$key] = ($v === '' || $v === null) ? null : (string) $v;
        }
        if (array_key_exists('search', $merged)) {
            $v = $merged['search'];
            $out['search'] = ($v === '' || $v === null) ? null : (string) $v;
        }

        return $out;
    }

    /**
     * مصفوفة جاهزة لـ route/querystring (بدون قيم فارغة).
     *
     * @return array<string, string>
     */
    public static function queryFromSession(Request $request): array
    {
        $stored = $request->session()->get(self::SESSION_KEY, []);
        $query = array_filter($stored, static fn ($v) => $v !== '' && $v !== null);

        if (isset($query[self::PAGE_KEY]) && (int) $query[self::PAGE_KEY] <= 1) {
            unset($query[self::PAGE_KEY]);
        }

        return $query;
    }

    /**
     * رابط قائمة الطلاب مع الفلاتر ورقم الصفحة المحفوظين.
     *
     * @param  array<string, string>|null  $merged
     */
    public static function indexUrl(Request $request, ?array $merged = null): string
    {
        $params = $merged !== null
            ? self::queryParamsFromMerged($merged)
            : self::queryFromSession($request);

        return route('students.index', $params);
    }

    /**
     * رابط صفحة الإحصائيات مع الفلاتر المحفوظة.
     *
     * @param  array<string, string>|null  $merged
     */
    public static function statisticsUrl(Request $request, ?array $merged = null): string
    {
        $params = $merged !== null
            ? self::queryParamsFromMerged($merged)
            : self::queryFromSession($request);

        unset($params[self::PAGE_KEY]);

        return route('students.statistics.index', $params);
    }

    /**
     * @param  array<string, string>  $merged
     * @return array<string, string>
     */
    private static function queryParamsFromMerged(array $merged): array
    {
        $params = array_filter($merged, static fn ($v) => $v !== '' && $v !== null);

        if (isset($params[self::PAGE_KEY]) && (int) $params[self::PAGE_KEY] <= 1) {
            unset($params[self::PAGE_KEY]);
        }

        return $params;
    }

    private static function filterOrSearchSubmitted(Request $request): bool
    {
        foreach (self::FILTER_KEYS as $key) {
            if ($request->query->has($key)) {
                return true;
            }
        }

        return $request->query->has('search');
    }
}
