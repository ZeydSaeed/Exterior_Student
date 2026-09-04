/**
 * صفحة التأييد: حفظ/تحديث كل حقول التأييد القابلة للتعديل:
 * العدد، التاريخ، الى، منظم التأييد، اسم الموظف، المسؤول، اسم الموظف المسؤول.
 */
(function () {
    'use strict';

    var form = document.getElementById('certificate-save-form');
    var printBtn = document.getElementById('certificate-btn-print');
    var saveBtn = document.getElementById('certificate-btn-save');
    if (!form || !printBtn) {
        return;
    }

    var isEdit = form.getAttribute('data-mode') === 'edit';
    var returnUrl = form.getAttribute('data-return-url') || '';

    function getText(el) {
        if (!el) {
            return '';
        }
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
            return (el.value || '').trim();
        }
        return (el.textContent || el.innerText || '').trim();
    }

    function toWesternDigits(str) {
        var arabic = '٠١٢٣٤٥٦٧٨٩';
        return String(str || '').replace(/[٠-٩]/g, function (d) {
            return String(arabic.indexOf(d));
        });
    }

    function parseDisplayDateToYmd(text) {
        var western = toWesternDigits(text).replace(/\s+/g, ' ').trim();
        var m = /^(\d{1,2})\s*[\/\-.]\s*(\d{1,2})\s*[\/\-.]\s*(\d{4})$/.exec(western);
        if (!m) {
            m = /^(\d{4})\s*[\/\-.]\s*(\d{1,2})\s*[\/\-.]\s*(\d{1,2})$/.exec(western);
            if (!m) {
                return '';
            }
            return m[1] + '-' + String(m[2]).padStart(2, '0') + '-' + String(m[3]).padStart(2, '0');
        }
        return m[3] + '-' + String(m[2]).padStart(2, '0') + '-' + String(m[1]).padStart(2, '0');
    }

    function getDateValue(el) {
        if (!el) {
            return '';
        }
        var fromText = parseDisplayDateToYmd(getText(el));
        if (fromText) {
            el.setAttribute('data-date', fromText);
            return fromText;
        }
        var dataDate = el.getAttribute('data-date');
        return dataDate ? dataDate.trim() : '';
    }

    function buildFormData() {
        var numEl = document.getElementById('cert-field-number');
        var issuedEl = document.getElementById('cert-field-issued-to');
        var dateEl = document.getElementById('cert-field-date');
        var rightTitleEl = document.getElementById('cert-field-right-title');
        var rightNameEl = document.getElementById('cert-field-right-name');
        var leftTitleEl = document.getElementById('cert-field-left-title');
        var leftNameEl = document.getElementById('cert-field-left-name');

        var formData = new FormData();
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            formData.append('_token', token.getAttribute('content'));
        }

        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        var typeInput = form.querySelector('input[name="type"]');
        formData.append('type', typeInput ? typeInput.value : 'without_grades');
        formData.append('date', dateEl ? getDateValue(dateEl) : '');
        formData.append('number', numEl ? toWesternDigits(getText(numEl)) : '');
        formData.append('issued_to', issuedEl ? getText(issuedEl) : '');
        formData.append('right_title', rightTitleEl ? getText(rightTitleEl) : '');
        formData.append('right_employee_name', rightNameEl ? getText(rightNameEl) : '');
        formData.append('left_title', leftTitleEl ? getText(leftTitleEl) : '');
        formData.append('left_employee_name', leftNameEl ? getText(leftNameEl) : '');

        return formData;
    }

    function submitAttestation() {
        return fetch(form.action, {
            method: 'POST',
            body: buildFormData(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
    }

    printBtn.addEventListener('click', function () {
        submitAttestation()
            .then(function () {
                if (typeof window.print === 'function') {
                    window.print();
                }
            })
            .catch(function () {
                if (typeof window.print === 'function') {
                    window.print();
                }
            });
    });

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            saveBtn.disabled = true;
            submitAttestation()
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('save_failed');
                    }
                    if (returnUrl) {
                        window.location.href = returnUrl;
                        return;
                    }
                    window.location.reload();
                })
                .catch(function () {
                    saveBtn.disabled = false;
                    window.alert('تعذر حفظ التعديلات. يرجى المحاولة مرة أخرى.');
                });
        });
    }
})();
