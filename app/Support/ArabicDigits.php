<?php

namespace App\Support;

/**
 * تحويل الأرقام بين العربية الهندية (٠١٢٣٤٥٦٧٨٩) والأرقام اللاتينية (0123456789).
 */
final class ArabicDigits
{
    private const ARABIC_INDIC = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    private const EASTERN_ARABIC_INDIC = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    public static function toArabic(string|int|float|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $str = (string) $value;
        $result = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = $str[$i];
            if ($c >= '0' && $c <= '9') {
                $result .= self::ARABIC_INDIC[(int) $c];
            } else {
                $result .= $c;
            }
        }

        return $result;
    }

    /**
     * تحويل الأرقام العربية/الفارسية إلى لاتينية قبل الحفظ أو التحقق.
     */
    public static function toWestern(string|int|float|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $str = (string) $value;
        $str = str_replace(self::ARABIC_INDIC, ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $str);

        return str_replace(self::EASTERN_ARABIC_INDIC, ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $str);
    }
}
