/**
 * صفحة سجل قيد الطالب — ملء الشاشة للمعاينة فقط (بدون المساس بطباعة القيد).
 */
(function () {
    'use strict';

    var printBtn = document.getElementById('student-document-btn-print');
    var layout = document.querySelector('.student-document-layout');
    var paper = document.querySelector('.student-document-paper');
    var content = document.querySelector('.page-student-document .dashboard-content');

    function clearDocumentFit() {
        if (paper) {
            paper.style.zoom = '';
            paper.style.transform = '';
            paper.style.transformOrigin = '';
        }
        if (content) {
            content.style.height = '';
            content.style.minHeight = '';
            content.style.maxHeight = '';
        }
    }

    function toolbarBottom() {
        var toolbar = document.querySelector('.dashboard-toolbar');
        if (!toolbar) {
            return 0;
        }
        return toolbar.getBoundingClientRect().bottom;
    }

    function applyContentHeight() {
        if (!content || (window.matchMedia && window.matchMedia('print').matches)) {
            return;
        }
        var h = Math.max(280, window.innerHeight - toolbarBottom());
        content.style.height = h + 'px';
        content.style.minHeight = h + 'px';
        content.style.maxHeight = h + 'px';
    }

    function fitDocumentToViewport() {
        if (!paper || !layout) {
            return;
        }
        if (window.matchMedia && window.matchMedia('print').matches) {
            clearDocumentFit();
            return;
        }

        applyContentHeight();

        paper.style.zoom = '';
        paper.style.transform = '';
        paper.style.transformOrigin = '';

        var naturalW = paper.offsetWidth;
        var naturalH = paper.offsetHeight;
        if (!naturalW || !naturalH) {
            return;
        }

        var actions = layout.querySelector('.student-document-actions');
        var availW;
        var availH;

        if (content) {
            var contentRect = content.getBoundingClientRect();
            var top = actions
                ? Math.max(contentRect.top, actions.getBoundingClientRect().bottom)
                : contentRect.top;
            availW = Math.max(240, contentRect.width - 8);
            availH = Math.max(240, window.innerHeight - top - 6);
        } else {
            var topFallback = actions ? actions.getBoundingClientRect().bottom : toolbarBottom();
            availW = Math.max(240, window.innerWidth - 24);
            availH = Math.max(240, window.innerHeight - topFallback - 12);
        }

        // تكبير/تصغير للمعاينة فقط — نفس حدود صفحة القيود؛ الطباعة تُصفَّر عبر clearDocumentFit
        var scale = Math.min(availW / naturalW, availH / naturalH);
        if (!isFinite(scale) || scale <= 0) {
            scale = 1;
        }
        scale = Math.max(0.25, Math.min(scale, 2.8));
        paper.style.zoom = String(scale);
    }

    function openPrint() {
        clearDocumentFit();
        if (typeof window.print === 'function') {
            window.print();
        }
    }

    if (printBtn) {
        printBtn.addEventListener('click', openPrint);
    }

    window.addEventListener('beforeprint', clearDocumentFit);
    window.addEventListener('afterprint', function () {
        fitDocumentToViewport();
    });
    window.addEventListener('resize', fitDocumentToViewport);

    function runFit() {
        fitDocumentToViewport();
        setTimeout(fitDocumentToViewport, 80);
        setTimeout(fitDocumentToViewport, 250);
        requestAnimationFrame(function () {
            requestAnimationFrame(fitDocumentToViewport);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runFit);
    } else {
        runFit();
    }

    if (!layout || !layout.getAttribute('data-update-url')) {
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
