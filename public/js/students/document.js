/**
 * صفحة سجل قيد الطالب — زر الطباعة وحفظ رقم الصفحة ورقم القيد.
 */
(function () {
    'use strict';

    var printBtn = document.getElementById('student-document-btn-print');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            if (typeof window.print === 'function') {
                window.print();
            }
        });
    }

    var layout = document.querySelector('.student-document-layout[data-update-url]');
    if (!layout) {
        return;
    }

    var updateUrl = layout.getAttribute('data-update-url');
    var pageInput = layout.querySelector('[data-page-input]');
    var enrollmentInput = layout.querySelector('[data-enrollment-input]');
    if (!updateUrl || (!pageInput && !enrollmentInput)) {
        return;
    }

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var saveTimer = null;
    var saving = false;

    function collectPayload() {
        return {
            page_number: pageInput ? pageInput.value : '',
            enrollment_number: enrollmentInput ? enrollmentInput.value : '',
        };
    }

    function saveEnrollmentFields() {
        if (saving) {
            return;
        }
        saving = true;
        window.fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(collectPayload()),
        }).finally(function () {
            saving = false;
        });
    }

    function scheduleSave() {
        if (saveTimer) {
            clearTimeout(saveTimer);
        }
        saveTimer = setTimeout(saveEnrollmentFields, 400);
    }

    [pageInput, enrollmentInput].forEach(function (input) {
        if (!input) {
            return;
        }
        input.addEventListener('input', scheduleSave);
        input.addEventListener('change', scheduleSave);
        input.addEventListener('blur', saveEnrollmentFields);
    });
})();
