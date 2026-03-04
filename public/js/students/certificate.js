(function () {
    'use strict';

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = value != null ? String(value) : '';
        }
    }

    function todayIso() {
        var d = new Date();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }

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

    // دعم فتح نموذج التأييد كمودال فوق جدول الطلاب (صفحة index)
    var certificateModal = document.getElementById('certificate-modal');
    var certificatePrintBtn = document.getElementById('certificate-btn-print');
    var certificateCloseBtn = document.querySelector('[data-certificate-modal-close]');

    function openCertificateModal() {
        if (!certificateModal) return;
        certificateModal.classList.add('is-visible');
        certificateModal.setAttribute('aria-hidden', 'false');
        fitCertificate();
    }

    function closeCertificateModal() {
        if (!certificateModal) return;
        certificateModal.classList.remove('is-visible');
        certificateModal.setAttribute('aria-hidden', 'true');
    }

    function fillCertificate(data) {
        if (!data) return;
        setText('certificate-exam-number', data.exam_number || '');
        setText('certificate-full-name', data.full_name || '');
        setText('certificate-birth-date', data.birth_date || '');
        setText('certificate-branch', data.branch || '');
        setText('certificate-specialization', data.specialization || '');
        setText('certificate-academic-year', data.academic_year || '');
        setText('certificate-result', data.result || '');
        setText('certificate-round', data.round || '');

        var employees = Array.isArray(data.employees) ? data.employees : [];
        var organizer = employees[0] || {};
        var manager = employees[1] || {};

        setText('certificate-organizer-title', organizer.type || 'منظم التأييد');
        setText('certificate-organizer-name', organizer.name || 'غير محدد');
        setText('certificate-manager-title', manager.type || 'مسؤول شعبة شؤون الطلبة');
        setText('certificate-manager-name', manager.name || 'غير محدد');

        var today = todayIso();
        setText('certificate-date', today);
        setText('certificate-organizer-date', today);
        setText('certificate-manager-date', today);

        if (window.ArabicDate && typeof window.ArabicDate.init === 'function') {
            window.ArabicDate.init();
        }
    }

    function attachCertificateButtons() {
        var urlTpl = window.STUDENTS_CERTIFICATE_URL_TEMPLATE || '/students/__ID__/certificate';
        var buttons = document.querySelectorAll('.btn-certificate-open');
        if (!buttons.length) {
            return;
        }
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.getAttribute('data-student-id');
                if (!id) return;
                openCertificateModal();
                var url = urlTpl.replace('__ID__', id);
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && !data.error) {
                            fillCertificate(data);
                        }
                    })
                    .catch(function () {
                        // في حالة الفشل نغلق المودال فقط
                        closeCertificateModal();
                        alert('تعذر تحميل بيانات التأييد. يرجى المحاولة مرة أخرى.');
                    });
            });
        });
    }

    // ربط أزرار الطباعة والإغلاق
    if (certificatePrintBtn) {
        certificatePrintBtn.addEventListener('click', openPrintDialog);
    }
    if (certificateCloseBtn) {
        certificateCloseBtn.addEventListener('click', closeCertificateModal);
    }
    if (certificateModal) {
        certificateModal.addEventListener('click', function (e) {
            if (e.target === certificateModal) {
                closeCertificateModal();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            fitCertificate();
            attachCertificateButtons();
        });
    } else {
        fitCertificate();
        attachCertificateButtons();
    }
    window.addEventListener('resize', fitCertificate);
})();
