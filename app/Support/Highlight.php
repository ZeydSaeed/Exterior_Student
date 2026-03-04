<?php

namespace App\Support;

/**
 * عرض آمن لتلوين نص البحث في الواجهة (بدون منطق في الـ View).
 */
final class Highlight
{
    public static function render(?string $text, ?string $pattern): string
    {
        $safe = e((string) $text);

        if ($pattern === null || $pattern === '') {
            return $safe;
        }

        return (string) preg_replace($pattern, '<mark>$0</mark>', $safe);
    }
}
