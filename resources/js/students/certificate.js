/**
 * صفحة التأييد — زر طباعة فقط بدون فتح تلقائي
 */
(function () {
    'use strict';

    function openPrintDialog() {
        if (typeof window.print === 'function') {
            window.print();
        }
    }

    function fitCertificate() {
        var content = document.querySelector('.support-paper');
        if (!content) return;
        content.style.transform = '';
        content.style.transformOrigin = '';
        var rect = content.getBoundingClientRect();
        var available = window.innerHeight - rect.top - 16;
        var natural = content.scrollHeight;
        if (!natural || available <= 0) return;
        var scale = Math.min(1, available / natural);
        if (scale < 0.7) scale = 0.7;
        content.style.transformOrigin = 'top center';
        content.style.transform = 'scale(' + scale + ')';
    }

    var printBtn = document.getElementById('certificate-btn-print');
    if (printBtn) {
        printBtn.addEventListener('click', openPrintDialog);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fitCertificate);
    } else {
        fitCertificate();
    }
    window.addEventListener('resize', fitCertificate);
})();
