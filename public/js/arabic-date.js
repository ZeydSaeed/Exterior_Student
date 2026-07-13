/**
 * تحويل التواريخ إلى العربية: أرقام عربية، تنسيق يوم / شهر / سنة، واتجاه من اليمين لليسار
 */
(function () {
    'use strict';

    var ARABIC_DIGITS = '٠١٢٣٤٥٦٧٨٩';

    /**
     * تحويل الأرقام الإنجليزية إلى عربية
     * @param {string|number} str
     * @returns {string}
     */
    function toArabicDigits(str) {
        if (str === null || str === undefined) return '';
        var s = String(str);
        var out = '';
        for (var i = 0; i < s.length; i++) {
            var c = s.charAt(i);
            if (c >= '0' && c <= '9') {
                out += ARABIC_DIGITS.charAt(parseInt(c, 10));
            } else {
                out += c;
            }
        }
        return out;
    }

    /**
     * إرجاع يوم وشهر وسنة من نص تاريخ (يدعم عدة صيغ)
     * @param {string} dateStr - مثل 2025-03-15 أو 15/3/2025 أو 15-03-2025
     * @returns {{ day: string, month: string, year: string }|null}
     */
    function parseDate(dateStr) {
        if (!dateStr || typeof dateStr !== 'string') return null;
        var s = dateStr.trim().replace(/\s+/g, ' ');
        var parts = s.split(/[\/\-\.]/);
        if (parts.length !== 3) return null;
        var a = parseInt(parts[0], 10);
        var b = parseInt(parts[1], 10);
        var c = parseInt(parts[2], 10);
        if (isNaN(a) || isNaN(b) || isNaN(c)) return null;
        var day, month, year;
        if (c > 31) {
            year = c;
            if (a > 12) {
                day = a;
                month = b;
            } else if (b > 12) {
                day = b;
                month = a;
            } else {
                day = a;
                month = b;
            }
        } else {
            year = a > 31 ? a : (c > 31 ? c : (a >= 100 ? a : c));
            if (a > 31) {
                month = b;
                day = c;
            } else if (c > 31) {
                day = a;
                month = b;
            } else if (a > 12 && b <= 12) {
                day = a;
                month = b;
            } else if (b > 12 && a <= 12) {
                day = b;
                month = a;
            } else {
                day = a;
                month = b;
            }
        }
        var d = day < 10 ? '0' + day : String(day);
        var m = month < 10 ? '0' + month : String(month);
        return { day: d, month: m, year: String(year) };
    }

    /**
     * تنسيق تاريخ بصيغة يوم / شهر / سنة بأرقام عربية
     * @param {{ day: string, month: string, year: string }} obj
     * @returns {string}
     */
    function formatDateDayMonthYear(obj) {
        if (!obj) return '';
        return toArabicDigits(obj.day) + ' / ' + toArabicDigits(obj.month) + ' / ' + toArabicDigits(obj.year);
    }

    /**
     * تحويل نص تاريخ إلى تنسيق عربي (يوم / شهر / سنة) بأرقام عربية
     * @param {string} dateStr
     * @returns {string}
     */
    function formatDateArabic(dateStr) {
        var parsed = parseDate(dateStr);
        return parsed ? formatDateDayMonthYear(parsed) : toArabicDigits(dateStr);
    }

    /**
     * تطبيق تنسيق التاريخ العربي على عنصر واحد
     * @param {Element} el
     */
    function applyToElement(el) {
        var raw = el.getAttribute('data-date') || el.textContent || '';
        var formatted = formatDateArabic(raw);
        if (formatted) {
            el.textContent = formatted;
            // اتجاه LTR يثبت ترتيب يوم / شهر / سنة بصرياً داخل صفحات RTL
            el.setAttribute('dir', 'ltr');
            el.classList.add('arabic-date-applied');
        }
    }

    /**
     * تطبيق تحويل الأرقام إلى عربية على عنصر واحد (للأعداد مثل الرقم الامتحاني، العام الدراسي، العدد)
     * @param {Element} el
     */
    function applyNumberElement(el) {
        var raw = el.getAttribute('data-number') || el.textContent || '';
        var formatted = toArabicDigits(raw);
        if (formatted) {
            el.textContent = formatted;
            el.setAttribute('dir', 'rtl');
            el.classList.add('arabic-number-applied');
        }
    }

    /**
     * تهيئة كل العناصر ذات الصف أو السمة المحددة
     */
    function init() {
        // تواريخ بصيغة يوم / شهر / سنة
        var dateList = document.querySelectorAll('[data-arabic-date], .arabic-date');
        for (var i = 0; i < dateList.length; i++) {
            applyToElement(dateList[i]);
        }
        // أرقام عادية (مثل الرقم الامتحاني، العدد، العام الدراسي)
        var numList = document.querySelectorAll('[data-arabic-number], .arabic-number');
        for (var j = 0; j < numList.length; j++) {
            var el = numList[j];
            applyNumberElement(el);
            // إذا كان قابلاً للتحرير، حوّل أي أرقام تُكتب لاحقاً مباشرة إلى عربية
            if (el.isContentEditable) {
                el.addEventListener('input', function (e) {
                    var target = e.currentTarget;
                    var text = target.textContent || '';
                    var converted = toArabicDigits(text);
                    if (text !== converted) {
                        target.textContent = converted;
                    }
                });
            }
        }
    }

    function run() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    }

    window.ArabicDate = {
        toArabicDigits: toArabicDigits,
        parseDate: parseDate,
        formatDateArabic: formatDateArabic,
        formatDateDayMonthYear: formatDateDayMonthYear,
        applyToElement: applyToElement,
        applyNumberElement: applyNumberElement,
        init: init
    };

    run();
})();
