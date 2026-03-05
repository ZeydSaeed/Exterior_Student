/**
 * صفحة التأييد (تأييد بدون درجات) — طباعة + تكيف بالعرض
 * يُحمّل من صفحة التأييد فقط (داخل الداشبورد، لا مودال).
 */
(function () {
    'use strict';

    function openPrintDialog() {
        if (typeof window.print === 'function') {
            window.print();
        }
    }

    /**
     * حساب نهاية التولبار وارتفاع منطقة التأييد: من نهاية التولبار إلى نهاية الداشبورد.
     */
    function getToolbarEndAndContentHeight() {
        var toolbar = document.querySelector('.dashboard-toolbar');
        if (!toolbar) return { contentHeight: window.innerHeight, toolbarHeight: 0 };
        var rect = toolbar.getBoundingClientRect();
        var toolbarEnd = rect.bottom;
        var contentHeight = window.innerHeight - toolbarEnd;
        return { contentHeight: contentHeight > 0 ? contentHeight : 0, toolbarHeight: rect.height };
    }

    function applyDashboardHeight() {
        var dashboardContent = document.querySelector('.page-certificate .dashboard-content');
        if (!dashboardContent) return;
        var result = getToolbarEndAndContentHeight();
        if (result.contentHeight > 0) {
            dashboardContent.style.height = result.contentHeight + 'px';
            dashboardContent.style.minHeight = result.contentHeight + 'px';
            dashboardContent.style.maxHeight = result.contentHeight + 'px';
        }
    }

    function fitCertificate() {
        var content = document.querySelector('.support-paper');
        var wrapper = content ? content.closest('.certificate-fit-wrapper') || content.parentElement : null;
        if (!content) return;
        content.style.transform = '';
        content.style.transformOrigin = '';

        var naturalHeight = content.scrollHeight;
        var naturalWidth = content.scrollWidth || content.offsetWidth;
        if (!naturalHeight || !naturalWidth) return;

        var dashboardContent = document.querySelector('.page-certificate .dashboard-content');
        var padding = 8;
        var availableHeight, availableWidth;
        if (dashboardContent && dashboardContent.offsetHeight > padding && dashboardContent.offsetWidth > padding) {
            availableHeight = dashboardContent.offsetHeight - padding;
            availableWidth = dashboardContent.offsetWidth - padding;
        } else {
            var result = getToolbarEndAndContentHeight();
            availableHeight = result.contentHeight - padding;
            availableWidth = window.innerWidth - padding * 2;
        }
        if (availableHeight <= 0) availableHeight = 400;
        if (availableWidth <= 0) availableWidth = 400;

        var scaleH = availableHeight / naturalHeight;
        var scaleW = availableWidth / naturalWidth;
        var scale = scaleW < scaleH ? scaleW : scaleH;
        if (scale > 1) scale = 1;
        if (scale < 0.15) scale = 0.15;

        content.style.transformOrigin = 'top left';
        content.style.transform = 'scale(' + scale + ')';

        if (wrapper) {
            wrapper.style.width = (naturalWidth * scale) + 'px';
            wrapper.style.height = (naturalHeight * scale) + 'px';
        }
    }

    var printBtn = document.getElementById('certificate-btn-print');
    if (printBtn) {
        printBtn.addEventListener('click', openPrintDialog);
    }

    function runFit() {
        applyDashboardHeight();
        fitCertificate();
        if (document.querySelector('.certificate-page')) {
            setTimeout(function () {
                applyDashboardHeight();
                fitCertificate();
            }, 50);
            setTimeout(function () {
                applyDashboardHeight();
                fitCertificate();
            }, 200);
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    applyDashboardHeight();
                    fitCertificate();
                });
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runFit);
    } else {
        runFit();
    }
    window.addEventListener('resize', function () {
        applyDashboardHeight();
        fitCertificate();
    });
})();
