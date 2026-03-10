/**
 * صفحة سجل قيد الطالب — زر الطباعة وتكيف العرض (منفصل عن certificate.js).
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
})();
