<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Support\Facades\Schema;

/**
 * كاش داخل العملية لوجود الجداول والأعمدة حتى لا يُستعلم INFORMATION_SCHEMA في كل طلب.
 */
final class CachedSchema
{
    /** @var array<string, bool> */
    private static array $tables = [];

    /** @var array<string, array<string, bool>> */
    private static array $columns = [];

    public static function reset(): void
    {
        self::$tables = [];
        self::$columns = [];
    }

    public static function hasTable(string $table): bool
    {
        return self::$tables[$table] ??= Schema::hasTable($table);
    }

    public static function hasColumn(string $table, string $column): bool
    {
        self::$columns[$table] ??= [];

        return self::$columns[$table][$column] ??= Schema::hasColumn($table, $column);
    }

    public static function usesNormalizedStudentSchema(): bool
    {
        return self::hasTable('students') || ! self::hasTable('main_table');
    }
}
