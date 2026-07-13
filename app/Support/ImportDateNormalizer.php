<?php

namespace App\Support;

/**
 * تطبيع تواريخ الاستيراد من Excel إلى Y-m-d مع دعم يوم/شهر/سنة بالشرطة المائلة.
 */
final class ImportDateNormalizer
{
    /**
     * @param  mixed  $value  نص، رقم تسلسلي لـ Excel، أو DateTime
     */
    public static function toYmd(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            $days = (int) round((float) $value);
            if ($days >= 1 && $days <= 100000) {
                $base = new \DateTimeImmutable('1899-12-30');

                return $base->add(new \DateInterval("P{$days}D"))->format('Y-m-d');
            }
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        // قيم قاعدة البيانات مثل 2006-06-15 أو 2006-06-15 00:00:00
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $str, $m) === 1) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // 15 / 06 / 2006 → 15/06/2006
        $str = preg_replace('/\s*([\/\-.])\s*/u', '$1', $str) ?? $str;

        if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/', $str, $m) === 1) {
            $first = (int) $m[1];
            $second = (int) $m[2];
            $year = (int) $m[3];

            // تفضيل يوم/شهر/سنة (مثل 25/4/1990)
            if (checkdate($second, $first, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $second, $first);
            }

            // احتياطي شهر/يوم/سنة (مثل 4/25/1990)
            if (checkdate($first, $second, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $first, $second);
            }

            return null;
        }

        if (preg_match('/^(\d{4})[\/\-.](\d{1,2})[\/\-.](\d{1,2})$/', $str, $m) === 1) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }

            return null;
        }

        $ts = strtotime($str);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d', $ts);
    }

    /**
     * تنسيق العرض كما في التأييدات: يوم / شهر / سنة (مثل 15 / 06 / 2006).
     */
    public static function toDisplayDmy(mixed $value): string
    {
        $ymd = self::toYmd($value);
        if ($ymd === null) {
            return '';
        }

        [$year, $month, $day] = explode('-', $ymd);

        return sprintf('%02d / %02d / %04d', (int) $day, (int) $month, (int) $year);
    }
}
