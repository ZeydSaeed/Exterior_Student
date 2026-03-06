<?php

namespace App\Application\Service;

/**
 * تحويل الأرقام إلى كتابة عربية (واحد، اثنان، ... عشرة، أحد عشر، ... مائة، ألف، إلخ).
 * يدعم من 0 حتى 9999.
 */
final class NumberToArabicWordsConverter
{
    private const ONES = [
        0 => 'صفر',
        1 => 'واحد',
        2 => 'اثنان',
        3 => 'ثلاثة',
        4 => 'أربعة',
        5 => 'خمسة',
        6 => 'ستة',
        7 => 'سبعة',
        8 => 'ثمانية',
        9 => 'تسعة',
    ];

    private const TENS = [
        2 => 'عشرون',
        3 => 'ثلاثون',
        4 => 'أربعون',
        5 => 'خمسون',
        6 => 'ستون',
        7 => 'سبعون',
        8 => 'ثمانون',
        9 => 'تسعون',
    ];

    private const TEN_TO_NINETEEN = [
        10 => 'عشرة',
        11 => 'أحد عشر',
        12 => 'اثنا عشر',
        13 => 'ثلاثة عشر',
        14 => 'أربعة عشر',
        15 => 'خمسة عشر',
        16 => 'ستة عشر',
        17 => 'سبعة عشر',
        18 => 'ثمانية عشر',
        19 => 'تسعة عشر',
    ];

    private const HUNDREDS = [
        1 => 'مائة',
        2 => 'مئتان',
        3 => 'ثلاثمائة',
        4 => 'أربعمائة',
        5 => 'خمسمائة',
        6 => 'ستمائة',
        7 => 'سبعمائة',
        8 => 'ثمانمائة',
        9 => 'تسعمائة',
    ];

    /**
     * تحويل رقم صحيح إلى كتابة عربية.
     *
     * @param int|string $number الرقم (يُحوَّل إلى int، 0-9999)
     */
    public function convert(int|string $number): string
    {
        $n = (int) $number;
        if ($n < 0 || $n > 9999) {
            return (string) $n;
        }
        if ($n === 0) {
            return self::ONES[0];
        }
        $parts = [];
        $thousands = (int) floor($n / 1000);
        if ($thousands > 0) {
            if ($thousands === 1) {
                $parts[] = 'ألف';
            } elseif ($thousands === 2) {
                $parts[] = 'ألفان';
            } elseif ($thousands >= 3 && $thousands <= 10) {
                $parts[] = self::ONES[$thousands] . ' آلاف';
            } else {
                $parts[] = $this->convertUpTo999($thousands) . ' ألف';
            }
            $n -= $thousands * 1000;
        }
        if ($n > 0) {
            $parts[] = $this->convertUpTo999($n);
        }
        return implode(' و', $parts);
    }

    private function convertUpTo999(int $n): string
    {
        if ($n >= 100) {
            $h = (int) floor($n / 100);
            $n -= $h * 100;
            $str = self::HUNDREDS[$h];
            if ($n > 0) {
                $str .= ' و' . $this->convertUpTo99($n);
            }
            return $str;
        }
        return $this->convertUpTo99($n);
    }

    private function convertUpTo99(int $n): string
    {
        if ($n >= 10 && $n <= 19) {
            return self::TEN_TO_NINETEEN[$n];
        }
        $ones = $n % 10;
        $tens = (int) floor($n / 10);
        if ($tens === 0) {
            return self::ONES[$ones];
        }
        if ($ones === 0) {
            return self::TENS[$tens];
        }
        return self::ONES[$ones] . ' و' . self::TENS[$tens];
    }
}
