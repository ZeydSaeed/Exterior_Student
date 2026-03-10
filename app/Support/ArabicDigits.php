<?php

namespace App\Support;

/**
 * تحويل الأرقام إلى أرقام عربية (٠١٢٣٤٥٦٧٨٩) للعرض في سجل القيد والتأييد.
 */
final class ArabicDigits
{
    private const ARABIC_INDIC = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

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
}
