<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Support\Facades\Cache;

/**
 * كاش عدد صفحات قائمة الطلاب حتى لا يُعاد COUNT(*) عند كل فتح سريع لنفس الفلاتر.
 */
final class StudentListQueryCache
{
    public const VERSION_KEY = 'student_list.cache_version';

    public const COUNT_TTL_SECONDS = 90;

    public const PAGE_TTL_SECONDS = 45;

    /** @var list<string> */
    private const CATALOG_KEYS = [
        'student_list.catalog.branches.name_ar',
        'student_list.catalog.majors.name_ar',
        'student_list.catalog.academic_years.year_label',
        'student_list.catalog.result_types.name_ar',
        'student_list.catalog.round_options.name_ar',
        'student_list.catalog.majors.map',
    ];

    public static function bump(): void
    {
        $current = (int) Cache::get(self::VERSION_KEY, 1);
        Cache::forever(self::VERSION_KEY, $current + 1);
        foreach (self::CATALOG_KEYS as $key) {
            Cache::forget($key);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  callable(): int  $callback
     */
    public static function rememberCount(array $filters, callable $callback): int
    {
        $normalized = $filters;
        ksort($normalized);

        $version = (int) Cache::get(self::VERSION_KEY, 1);
        $key = 'student_list.count.'.$version.'.'.md5((string) json_encode($normalized));

        return (int) Cache::remember($key, self::COUNT_TTL_SECONDS, $callback);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  callable(): list<array<string, mixed>>  $callback
     * @return list<array<string, mixed>>
     */
    public static function rememberPage(array $filters, int $page, callable $callback): array
    {
        $normalized = $filters;
        ksort($normalized);

        $version = (int) Cache::get(self::VERSION_KEY, 1);
        $key = 'student_list.page.'.$version.'.'.md5((string) json_encode([$normalized, $page]));
        $rows = Cache::remember($key, self::PAGE_TTL_SECONDS, $callback);

        return is_array($rows) ? $rows : [];
    }
}
