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
        return { day: String(day), month: String(month), year: String(year) };
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
     * تنسيق قيمة input[type=date] (YYYY-MM-DD) إلى يوم / شهر / سنة بأرقام عربية
     * @param {string} ymd
     * @returns {string}
     */
    function formatYmdArabic(ymd) {
        if (!ymd || typeof ymd !== 'string') {
            return '';
        }
        var m = /^(\d{4})-(\d{1,2})-(\d{1,2})$/.exec(ymd.trim());
        if (!m) {
            return formatDateArabic(ymd);
        }
        return formatDateDayMonthYear({
            day: String(parseInt(m[3], 10)),
            month: String(parseInt(m[2], 10)),
            year: m[1],
        });
    }

    /**
     * تحويل الأرقام العربية إلى إنجليزية
     * @param {string|number} str
     * @returns {string}
     */
    function toWesternDigits(str) {
        if (str === null || str === undefined) {
            return '';
        }
        var s = String(str);
        var out = '';
        for (var i = 0; i < s.length; i++) {
            var c = s.charAt(i);
            var idx = ARABIC_DIGITS.indexOf(c);
            if (idx >= 0) {
                out += String(idx);
            } else {
                out += c;
            }
        }
        return out;
    }

    /**
     * أرقام فقط (عربية أو إنجليزية) ثم تحويلها لعربية للعرض
     * @param {string} str
     * @param {number} maxLen
     * @returns {string}
     */
    function sanitizeDigitField(str, maxLen) {
        var western = toWesternDigits(str || '').replace(/\D/g, '');
        if (maxLen > 0 && western.length > maxLen) {
            western = western.slice(0, maxLen);
        }
        return toArabicDigits(western);
    }

    function pad2(n) {
        var s = String(n);
        return s.length === 1 ? '0' + s : s;
    }

    /**
     * بناء YYYY-MM-DD من يوم/شهر/سنة إن كانت صالحة
     * @param {string} dayAr
     * @param {string} monthAr
     * @param {string} yearAr
     * @returns {string}
     */
    function buildYmd(dayAr, monthAr, yearAr) {
        var d = parseInt(toWesternDigits(dayAr), 10);
        var m = parseInt(toWesternDigits(monthAr), 10);
        var y = parseInt(toWesternDigits(yearAr), 10);
        if (!y || y < 1000 || y > 9999 || !m || m < 1 || m > 12 || !d || d < 1 || d > 31) {
            return '';
        }
        var dt = new Date(y, m - 1, d);
        if (dt.getFullYear() !== y || dt.getMonth() !== m - 1 || dt.getDate() !== d) {
            return '';
        }
        return y + '-' + pad2(m) + '-' + pad2(d);
    }

    /**
     * حقل type="date" مع إدخال يدوي يوم / شهر / سنة (+ منتقي التقويم)
     * @param {HTMLInputElement} input
     */
    function enhanceDateInput(input) {
        if (!input || input.type !== 'date' || input.dataset.arabicDateEnhanced === '1') {
            return;
        }
        input.dataset.arabicDateEnhanced = '1';
        input.setAttribute('lang', 'ar-IQ');
        input.classList.add('arabic-date-field');

        var wrap = document.createElement('div');
        wrap.className = 'arabic-date-field-wrap';
        wrap.setAttribute('dir', 'rtl');

        var dmy = document.createElement('div');
        dmy.className = 'arabic-date-dmy';
        dmy.setAttribute('dir', 'rtl');

        function makePart(cls, maxLen, placeholder) {
            var el = document.createElement('input');
            el.type = 'text';
            el.className = 'arabic-date-part ' + cls;
            el.setAttribute('inputmode', 'numeric');
            el.setAttribute('maxlength', String(maxLen));
            el.setAttribute('placeholder', placeholder);
            el.setAttribute('autocomplete', 'off');
            el.setAttribute('dir', 'rtl');
            el.setAttribute('aria-label', placeholder);
            return el;
        }

        var dayInput = makePart('arabic-date-day', 2, 'يوم');
        var monthInput = makePart('arabic-date-month', 2, 'شهر');
        var yearInput = makePart('arabic-date-year', 4, 'سنة');
        var sep1 = document.createElement('span');
        sep1.className = 'arabic-date-sep';
        sep1.textContent = '/';
        var sep2 = document.createElement('span');
        sep2.className = 'arabic-date-sep';
        sep2.textContent = '/';

        // ترتيب من اليمين لليسار: يوم / شهر / سنة
        dmy.appendChild(dayInput);
        dmy.appendChild(sep1);
        dmy.appendChild(monthInput);
        dmy.appendChild(sep2);
        dmy.appendChild(yearInput);

        var parent = input.parentNode;
        if (!parent) {
            return;
        }
        parent.insertBefore(wrap, input);
        wrap.appendChild(dmy);
        wrap.appendChild(input);

        var syncing = false;

        function fillFromDateValue() {
            var value = input.value || '';
            var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
            if (!m) {
                dayInput.value = '';
                monthInput.value = '';
                yearInput.value = '';
                wrap.classList.add('is-empty');
                return;
            }
            dayInput.value = toArabicDigits(String(parseInt(m[3], 10)));
            monthInput.value = toArabicDigits(String(parseInt(m[2], 10)));
            yearInput.value = toArabicDigits(m[1]);
            wrap.classList.remove('is-empty');
        }

        function writeToDateInput() {
            if (syncing) {
                return;
            }
            var ymd = buildYmd(dayInput.value, monthInput.value, yearInput.value);
            syncing = true;
            if (ymd) {
                input.value = ymd;
                wrap.classList.remove('is-empty');
            } else if (!toWesternDigits(dayInput.value) && !toWesternDigits(monthInput.value) && !toWesternDigits(yearInput.value)) {
                input.value = '';
                wrap.classList.add('is-empty');
            }
            syncing = false;
        }

        function autoSizePart(part, maxLen) {
            var len = toWesternDigits(part.value || '').length;
            if (len < 1) {
                len = Math.min(2, maxLen);
            }
            // مساحة أوسع قليلاً من عدد الأرقام لراحة العرض
            part.style.width = (len + 0.6) + 'ch';
        }

        function autoSizeAllParts() {
            autoSizePart(dayInput, 2);
            autoSizePart(monthInput, 2);
            autoSizePart(yearInput, 4);
        }

        function onPartInput(part, maxLen, next) {
            return function () {
                var before = part.value;
                part.value = sanitizeDigitField(before, maxLen);
                autoSizePart(part, maxLen);
                writeToDateInput();
                var westernLen = toWesternDigits(part.value).length;
                if (westernLen >= maxLen && next) {
                    next.focus();
                    next.select();
                }
            };
        }

        dayInput.addEventListener('input', onPartInput(dayInput, 2, monthInput));
        monthInput.addEventListener('input', onPartInput(monthInput, 2, yearInput));
        yearInput.addEventListener('input', onPartInput(yearInput, 4, null));

        [dayInput, monthInput, yearInput].forEach(function (part) {
            part.addEventListener('blur', writeToDateInput);
            part.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !toWesternDigits(part.value) && part !== dayInput) {
                    e.preventDefault();
                    var prev = part === yearInput ? monthInput : dayInput;
                    prev.focus();
                }
            });
        });

        input.addEventListener('change', function () {
            if (syncing) {
                return;
            }
            fillFromDateValue();
            autoSizeAllParts();
        });

        // النقر على أيقونة التقويم يفتح المنتقي الأصلي
        input.addEventListener('click', function () {
            if (typeof input.showPicker === 'function') {
                try {
                    input.showPicker();
                } catch (e) {
                    // ignore
                }
            }
        });

        fillFromDateValue();
        autoSizeAllParts();
    }

    /**
     * تحسين كل حقول التاريخ من نوع date داخل الجذر
     * @param {ParentNode} [root]
     */
    function enhanceDateInputs(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var list = scope.querySelectorAll('input[type="date"].arabic-date-field, input[type="date"][data-arabic-date-field]');
        for (var i = 0; i < list.length; i++) {
            enhanceDateInput(list[i]);
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
        enhanceDateInputs(document);
        enhanceArabicDigitInputs(document);
    }

    /**
     * تحويل أرقام حقول الإدخال إلى أرقام عربية أثناء الكتابة والعرض
     * @param {HTMLInputElement} input
     */
    function enhanceArabicDigitInput(input) {
        if (!input || input.dataset.arabicDigitsEnhanced === '1') {
            return;
        }
        input.dataset.arabicDigitsEnhanced = '1';
        input.setAttribute('dir', 'rtl');
        input.setAttribute('lang', 'ar');
        input.classList.add('arabic-digits-input');

        function convertValue() {
            var start = input.selectionStart;
            var end = input.selectionEnd;
            var before = input.value || '';
            var after = toArabicDigits(before);
            if (before === after) {
                return;
            }
            input.value = after;
            if (typeof start === 'number' && typeof end === 'number' && document.activeElement === input) {
                try {
                    input.setSelectionRange(start, end);
                } catch (e) {
                    // ignore
                }
            }
        }

        convertValue();
        input.addEventListener('input', convertValue);
        input.addEventListener('blur', convertValue);
        input.addEventListener('paste', function () {
            setTimeout(convertValue, 0);
        });
    }

    /**
     * @param {ParentNode} [root]
     */
    function enhanceArabicDigitInputs(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var list = scope.querySelectorAll('input.arabic-digits-input, input[data-arabic-digits]');
        for (var i = 0; i < list.length; i++) {
            enhanceArabicDigitInput(list[i]);
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
        toWesternDigits: toWesternDigits,
        parseDate: parseDate,
        formatDateArabic: formatDateArabic,
        formatDateDayMonthYear: formatDateDayMonthYear,
        formatYmdArabic: formatYmdArabic,
        applyToElement: applyToElement,
        applyNumberElement: applyNumberElement,
        enhanceDateInput: enhanceDateInput,
        enhanceDateInputs: enhanceDateInputs,
        enhanceArabicDigitInput: enhanceArabicDigitInput,
        enhanceArabicDigitInputs: enhanceArabicDigitInputs,
        init: init
    };

    run();
})();
