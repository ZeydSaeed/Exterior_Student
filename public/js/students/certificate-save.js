/**
 * صفحة التأييد: العدد و "الى" يُدخلان يدوياً. عند النقر على "طباعة" نقرأ النص المكتوب
 * فيهما من DOM ونرسله مع الطلب ليُحفظ في certificate.number و certificate.issued_to.
 */
(function () {
    'use strict';

    var form = document.getElementById('certificate-save-form');
    var printBtn = document.getElementById('certificate-btn-print');
    if (!form || !printBtn) return;

    function getText(el) {
        if (!el) return '';
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
            return (el.value || '').trim();
        }
        var t = (el.textContent || el.innerText || '').trim();
        return t;
    }

    function getDateValue(el) {
        if (!el) return '';
        var dataDate = el.getAttribute('data-date');
        if (dataDate) return dataDate.trim();
        return getText(el);
    }

    /** قراءة العدد و "الى" من العناصر القابلة للتحرير ثم بناء FormData كامل للإرسال */
    function buildFormData() {
        var numEl = document.getElementById('cert-field-number');
        var issuedEl = document.getElementById('cert-field-issued-to');
        var dateEl = document.getElementById('cert-field-date');
        var rightTitleEl = document.getElementById('cert-field-right-title');
        var rightNameEl = document.getElementById('cert-field-right-name');
        var leftTitleEl = document.getElementById('cert-field-left-title');
        var leftNameEl = document.getElementById('cert-field-left-name');

        var numberValue = numEl ? getText(numEl) : '';
        var issuedToValue = issuedEl ? getText(issuedEl) : '';

        var formData = new FormData();
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) formData.append('_token', token.getAttribute('content'));

        formData.append('type', form.querySelector('input[name="type"]') ? form.querySelector('input[name="type"]').value : 'without_grades');
        formData.append('date', dateEl ? getDateValue(dateEl) : '');
        formData.append('number', numberValue);
        formData.append('issued_to', issuedToValue);
        formData.append('right_title', rightTitleEl ? getText(rightTitleEl) : '');
        formData.append('right_employee_name', rightNameEl ? getText(rightNameEl) : '');
        formData.append('left_title', leftTitleEl ? getText(leftTitleEl) : '');
        formData.append('left_employee_name', leftNameEl ? getText(leftNameEl) : '');

        return formData;
    }

    printBtn.addEventListener('click', function () {
        var formData = buildFormData();
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(function () {
            if (typeof window.print === 'function') {
                window.print();
            }
        }).catch(function () {
            if (typeof window.print === 'function') {
                window.print();
            }
        });
    });
})();
