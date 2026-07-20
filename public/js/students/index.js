/**
 * صفحة بيانات الطلبة — مودال الدرجات + مودال التأييد
 * يعتمد على: window.STUDENTS_GRADES_URL_TEMPLATE, STUDENTS_GRADES_UPDATE_URL_TEMPLATE,
 * STUDENTS_CERTIFICATE_URL_TEMPLATE, STUDENTS_CSRF_TOKEN (تُعيّن من الصفحة)
 */
(function () {
    'use strict';

    var urlTpl = window.STUDENTS_GRADES_URL_TEMPLATE || '/students/__ID__/grades';
    var modal = document.getElementById('grades-modal');
    var form = document.getElementById('grades-form');
    var tbody = document.getElementById('grades-tbody');
    if (!modal || !form || !tbody) return;

    var btnEdit = document.getElementById('grades-btn-edit');
    var btnSave = document.getElementById('grades-btn-save');
    var btnCancel = document.getElementById('grades-btn-cancel');
    var openBtns = document.querySelectorAll('.btn-grades-open');
    var closeBtn = document.querySelector('[data-grades-modal-close]');
    var currentData = null;
    var originalData = null;

    function formatDisplayDate(value) {
        if (value == null || value === '') {
            return '';
        }
        var s = String(value).trim();
        var m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
        if (m) {
            var day = String(parseInt(m[3], 10));
            var month = String(parseInt(m[2], 10));
            return day + ' / ' + month + ' / ' + m[1];
        }
        return s;
    }

    function openModal() {
        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('grades-modal-open');
    }

    function closeModal() {
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('grades-modal-open');
        setEditMode(false);
    }

    function setEditMode(edit) {
        document.querySelectorAll('.grades-readonly').forEach(function (el) {
            el.style.display = edit ? 'none' : '';
        });
        document.querySelectorAll('.grades-edit').forEach(function (el) {
            el.style.display = edit ? 'block' : 'none';
            var isAutoSum = el.getAttribute('data-auto-sum') === '1';
            el.readOnly = !edit || isAutoSum;
            if (edit && !isAutoSum) el.removeAttribute('readonly');
            else el.setAttribute('readonly', 'readonly');
        });
        document.querySelectorAll('.grades-row-score').forEach(function (el) {
            el.readOnly = !edit;
            if (edit) el.removeAttribute('readonly');
            else el.setAttribute('readonly', 'readonly');
        });
        document.querySelectorAll('.grades-row-subject').forEach(function (el) {
            el.readOnly = true;
            el.setAttribute('readonly', 'readonly');
        });
        if (btnEdit) btnEdit.style.display = edit ? 'none' : '';
        if (btnSave) btnSave.style.display = edit ? '' : 'none';
        if (btnCancel) btnCancel.style.display = edit ? '' : 'none';
        if (edit) {
            recalculateTotal();
        }
    }

    /** المجموع بدالة الجمع من درجات المواد */
    function recalculateTotal() {
        var sum = 0;
        document.querySelectorAll('.grades-row-score').forEach(function (el) {
            var v = String(el.value || '').trim();
            if (v !== '' && !isNaN(v)) {
                sum += Math.round(Number(v));
            }
        });
        var totalStr = String(sum);
        setText('grades-total', totalStr);
        setVal('grades-total-input', totalStr);
    }

    function setText(id, v) {
        var e = document.getElementById(id);
        if (e) e.textContent = v != null ? String(v) : '';
    }

    function setVal(id, v) {
        var e = document.getElementById(id);
        if (e) e.value = v != null ? String(v) : '';
    }

    function addRow(i, sub, score) {
        var tr = document.createElement('tr');
        var td1 = document.createElement('td');
        var i1 = document.createElement('input');
        i1.type = 'text';
        i1.className = 'grades-row-subject';
        i1.name = 'grades[' + i + '][subject]';
        i1.value = sub != null ? String(sub) : '';
        i1.readOnly = true;
        td1.appendChild(i1);
        tr.appendChild(td1);
        var td2 = document.createElement('td');
        var i2 = document.createElement('input');
        i2.type = 'text';
        i2.className = 'grades-row-score';
        i2.name = 'grades[' + i + '][score]';
        i2.value = score != null ? String(score) : '';
        i2.readOnly = true;
        i2.addEventListener('input', recalculateTotal);
        i2.addEventListener('change', recalculateTotal);
        td2.appendChild(i2);
        tr.appendChild(td2);
        tbody.appendChild(tr);
    }

    function fillForm(data) {
        currentData = data;
        originalData = data && typeof data === 'object' ? JSON.parse(JSON.stringify(data)) : null;
        var sid = document.getElementById('grades-student-id');
        if (sid) sid.value = data.id || '';
        setText('grades-exam-number', data.exam_number);
        setVal('grades-exam-number-input', data.exam_number);
        setText('grades-name-student', data.name_student != null ? data.name_student : (data.full_name || ''));
        setVal('grades-name-student-input', data.name_student != null ? data.name_student : (data.full_name || ''));
        setText('grades-name-father', data.name_father != null ? data.name_father : '');
        setVal('grades-name-father-input', data.name_father != null ? data.name_father : '');
        setText('grades-name-grandfather', data.name_grandfather != null ? data.name_grandfather : '');
        setVal('grades-name-grandfather-input', data.name_grandfather != null ? data.name_grandfather : '');
        setText('grades-name-surname', data.name_surname != null ? data.name_surname : '');
        setVal('grades-name-surname-input', data.name_surname != null ? data.name_surname : '');
        setText('grades-birth-date', formatDisplayDate(data.birth_date));
        setVal('grades-birth-date-input', data.birth_date != null ? data.birth_date : '');
        setText('grades-birth-place', data.birth_place != null ? data.birth_place : '');
        setVal('grades-birth-place-input', data.birth_place != null ? data.birth_place : '');
        setText('grades-mother-full-name', data.mother_full_name != null ? data.mother_full_name : '');
        setVal('grades-mother-full-name-input', data.mother_full_name != null ? data.mother_full_name : '');
        setText('grades-gender', data.gender != null ? data.gender : '');
        setVal('grades-gender-input', data.gender != null ? data.gender : '');
        setText('grades-branch', data.branch);
        setVal('grades-branch-input', data.branch);
        setText('grades-major', data.major);
        setVal('grades-major-input', data.major);
        setText('grades-year', data.academic_year);
        setVal('grades-year-input', data.academic_year);
        setText('grades-average', data.average);
        setVal('grades-average-input', data.average);
        setText('grades-result', data.result);
        setVal('grades-result-input', data.result);
        setText('grades-round', data.round);
        setVal('grades-round-input', data.round);
        tbody.innerHTML = '';
        var g = data.grades || [];
        if (g.length === 0) {
            addRow(0, '', '');
            addRow(1, '', '');
            addRow(2, '', '');
        } else {
            g.forEach(function (r, i) {
                addRow(i, r.subject, r.score);
            });
        }
        recalculateTotal();
        setEditMode(false);
    }

    function openGradesFor(id, sourceBtn, editImmediately) {
        if (!id) return;
        openModal();
        var url = urlTpl.replace('__ID__', String(id));
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.error) fillForm(data);
                if (editImmediately) setEditMode(true);
            })
            .catch(function () {
                var btn = sourceBtn;
                fillForm({
                    id: id,
                    exam_number: btn ? btn.getAttribute('data-exam-number') : '',
                    name_student: btn ? (btn.getAttribute('data-name') || '') : '',
                    name_father: '',
                    name_grandfather: '',
                    name_surname: '',
                    birth_date: '',
                    birth_place: '',
                    mother_full_name: '',
                    gender: btn ? (btn.getAttribute('data-gender') || '') : '',
                    branch: btn ? btn.getAttribute('data-branch') : '',
                    major: btn ? btn.getAttribute('data-major') : '',
                    academic_year: btn ? btn.getAttribute('data-year') : '',
                    result: btn ? btn.getAttribute('data-result') : '',
                    total: '', average: '', round: '',
                    grades: [{ subject: '', score: '' }, { subject: '', score: '' }, { subject: '', score: '' }]
                });
                if (editImmediately) setEditMode(true);
            });
    }

    openBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-student-id');
            openGradesFor(id, this, false);
        });
    });

    document.querySelectorAll('.btn-edit-row').forEach(function (editBtn) {
        editBtn.addEventListener('click', function () {
            var row = this.closest('tr');
            if (!row) return;
            var gradesBtn = row.querySelector('.btn-grades-open');
            if (!gradesBtn) return;
            var id = gradesBtn.getAttribute('data-student-id');
            openGradesFor(id, gradesBtn, true);
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    if (btnEdit) btnEdit.addEventListener('click', function () { setEditMode(true); });
    if (btnCancel) btnCancel.addEventListener('click', function () {
        if (originalData) fillForm(originalData);
        else setEditMode(false);
    });

    function updateTableRow(studentId, payload) {
        var btn = document.querySelector('.btn-grades-open[data-student-id="' + studentId + '"]');
        if (!btn) return;
        var row = btn.closest('tr');
        if (!row || !row.cells || row.cells.length < 3) return;
        var fullName = [payload.name_student, payload.name_father, payload.name_grandfather, payload.name_surname].filter(Boolean).join(' ').trim() || payload.name_student || '';
        row.cells[1].textContent = payload.exam_number != null ? payload.exam_number : '';
        row.cells[2].textContent = fullName;
        if (row.setAttribute) {
            row.setAttribute('data-name', fullName);
            row.setAttribute('data-exam', payload.exam_number != null ? String(payload.exam_number) : '');
        }
        btn.setAttribute('data-name', fullName);
        btn.setAttribute('data-exam-number', payload.exam_number != null ? String(payload.exam_number) : '');
        btn.setAttribute('data-gender', payload.gender != null ? String(payload.gender) : '');
        btn.setAttribute('data-branch', payload.branch != null ? String(payload.branch) : '');
        btn.setAttribute('data-major', payload.major != null ? String(payload.major) : '');
        btn.setAttribute('data-year', payload.academic_year != null ? String(payload.academic_year) : '');
        btn.setAttribute('data-result', payload.result != null ? String(payload.result) : '');
    }

    if (btnSave) {
        btnSave.addEventListener('click', function () {
            if (!currentData || !form) return;
            recalculateTotal();
            var grades = [];
            tbody.querySelectorAll('tr').forEach(function (tr) {
                var sub = tr.querySelector('.grades-row-subject');
                var score = tr.querySelector('.grades-row-score');
                grades.push({ subject: sub ? sub.value : '', score: score ? score.value : '' });
            });
            var payload = {
                exam_number: (document.getElementById('grades-exam-number-input') || {}).value || '',
                name_student: (document.getElementById('grades-name-student-input') || {}).value || '',
                name_father: (document.getElementById('grades-name-father-input') || {}).value || '',
                name_grandfather: (document.getElementById('grades-name-grandfather-input') || {}).value || '',
                name_surname: (document.getElementById('grades-name-surname-input') || {}).value || '',
                birth_date: (document.getElementById('grades-birth-date-input') || {}).value || '',
                birth_place: (document.getElementById('grades-birth-place-input') || {}).value || '',
                mother_full_name: (document.getElementById('grades-mother-full-name-input') || {}).value || '',
                gender: (document.getElementById('grades-gender-input') || {}).value || '',
                branch: (document.getElementById('grades-branch-input') || {}).value || '',
                major: (document.getElementById('grades-major-input') || {}).value || '',
                academic_year: (document.getElementById('grades-year-input') || {}).value || '',
                total: (document.getElementById('grades-total-input') || {}).value || '',
                average: (document.getElementById('grades-average-input') || {}).value || '',
                result: (document.getElementById('grades-result-input') || {}).value || '',
                round: (document.getElementById('grades-round-input') || {}).value || '',
                grades: grades
            };
            var examNumber = String(payload.exam_number || '').trim();
            if (!examNumber) {
                var examInput = document.getElementById('grades-exam-number-input');
                if (examInput) {
                    examInput.focus();
                    examInput.classList.add('grades-input-error');
                    setTimeout(function () { examInput.classList.remove('grades-input-error'); }, 2000);
                }
                alert('الرقم الامتحاني مطلوب. يرجى إدخاله قبل الحفظ.');
                return;
            }
            var updateUrl = (window.STUDENTS_GRADES_UPDATE_URL_TEMPLATE || '/students/__ID__/grades').replace('__ID__', String(currentData.id));
            var csrf = window.STUDENTS_CSRF_TOKEN || (document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content')) || '';
            fetch(updateUrl, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload)
            })
                .then(function (r) {
                    return r.text().then(function (text) {
                        if (!r.ok) {
                            var err = new Error(text || 'HTTP ' + r.status);
                            err.status = r.status;
                            err.body = text;
                            throw err;
                        }
                        return text ? JSON.parse(text) : {};
                    });
                })
                .then(function () {
                    updateTableRow(currentData.id, payload);
                    closeModal();
                })
                .catch(function (err) {
                    var msg = 'تعذر حفظ التعديلات. تحقق من الاتصال وحاول مرة أخرى.';
                    if (err && err.status === 419) msg = 'انتهت الجلسة. يرجى تحديث الصفحة والمحاولة مرة أخرى.';
                    else if (err && err.status === 404) msg = 'لم يتم العثور على سجل الطالب.';
                    else if (err && err.body) try {
                        var d = JSON.parse(err.body);
                        if (d.message) msg = d.message;
                    } catch (e) {}
                    if (err && err.status) console.error('Save grades:', err.status, err.body);
                    alert(msg);
                });
        });
    }

    // ——— مودال التأييد ———
    (function () {
        var certModal = document.getElementById('certificate-modal');
        if (!certModal) return;
        var certButtons = document.querySelectorAll('.btn-certificate-open');
        var certPrintBtn = document.getElementById('certificate-btn-print');
        var certCloseBtn = document.querySelector('[data-certificate-modal-close]');
        var urlTplCert = window.STUDENTS_CERTIFICATE_URL_TEMPLATE || '/students/__ID__/certificate';

        function setCertText(id, value) {
            var el = document.getElementById(id);
            if (el) el.textContent = value != null ? String(value) : '';
        }

        function openCertModal() {
            certModal.classList.add('is-visible');
            certModal.setAttribute('aria-hidden', 'false');
        }

        function closeCertModal() {
            certModal.classList.remove('is-visible');
            certModal.setAttribute('aria-hidden', 'true');
        }

        function fillCert(data) {
            if (!data) return;
            setCertText('certificate-exam-number', data.exam_number || '');
            setCertText('certificate-full-name', data.full_name || '');
            setCertText('certificate-birth-date', formatDisplayDate(data.birth_date || ''));
            var birthDateEl = document.getElementById('certificate-birth-date');
            if (birthDateEl && data.birth_date) {
                birthDateEl.setAttribute('data-date', data.birth_date);
            }
            setCertText('certificate-branch', data.branch || '');
            setCertText('certificate-specialization', data.specialization || '');
            setCertText('certificate-academic-year', data.academic_year || '');
            setCertText('certificate-result', data.result || '');
            setCertText('certificate-round', data.round || '');
            var avgEl = document.getElementById('certificate-average');
            if (avgEl) {
                var avgRaw = data.average != null && String(data.average).trim() !== '' ? String(data.average).trim() : '—';
                avgEl.setAttribute('data-number', avgRaw === '—' ? '' : avgRaw);
                avgEl.textContent = avgRaw;
            }
            var employees = Array.isArray(data.employees) ? data.employees : [];
            var organizer = employees[0] || {};
            var manager = employees[1] || {};
            setCertText('certificate-organizer-title', organizer.type || 'منظم التأييد');
            setCertText('certificate-organizer-name', organizer.name || 'غير محدد');
            setCertText('certificate-manager-title', manager.type || 'مسؤول شعبة شؤون الطلبة');
            setCertText('certificate-manager-name', manager.name || 'غير محدد');
            if (window.ArabicDate && typeof window.ArabicDate.init === 'function') {
                window.ArabicDate.init();
            }
        }

        certButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.getAttribute('data-student-id');
                if (!id) return;
                openCertModal();
                var url = urlTplCert.replace('__ID__', id);
                fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && !data.error) fillCert(data);
                    })
                    .catch(function () {
                        closeCertModal();
                        alert('تعذر تحميل بيانات التأييد. يرجى المحاولة مرة أخرى.');
                    });
            });
        });

        if (certPrintBtn) {
            certPrintBtn.addEventListener('click', function () {
                if (typeof window.print === 'function') window.print();
            });
        }
        if (certCloseBtn) certCloseBtn.addEventListener('click', closeCertModal);
        certModal.addEventListener('click', function (e) { if (e.target === certModal) closeCertModal(); });

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
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fitCertificate);
        } else {
            fitCertificate();
        }
        window.addEventListener('resize', fitCertificate);
    })();

    /** رابط «القيود» يفتح صفحة فارغة قابلة للطباعة؛ الفلترة تتم داخل صفحة القيود */
    (function syncBulkPrintLinks() {
        var base = window.STUDENTS_BULK_PRINT_URL;
        if (!base) return;
        function updateLinks() {
            var toolbar = document.getElementById('toolbar-link-bulk-print');
            var sidebar = document.getElementById('sidebar-link-bulk-print');
            if (toolbar) toolbar.setAttribute('href', base);
            if (sidebar) sidebar.setAttribute('href', base);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', updateLinks);
        } else {
            updateLinks();
        }
    })();

    /** حذف الطلبة الراسبين/المعيدين بحسب فلاتر السايدبار */
    (function failuresBulkDelete() {
        var link = document.getElementById('sidebar-link-failures');
        var modal = document.getElementById('failures-modal');
        if (!link || !modal) return;

        var confirmBtn = document.getElementById('failures-btn-confirm');
        var closeButtons = modal.querySelectorAll('[data-failures-modal-close]');
        var filterForm = document.getElementById('students-filter-form');

        function getFilterLabel(name) {
            if (!filterForm) return 'الكل';
            var el = filterForm.querySelector('select[name="' + name + '"]');
            if (!el) return 'الكل';
            var opt = el.options[el.selectedIndex];
            if (!opt) return 'الكل';
            var txt = (opt.textContent || '').replace(/\s+/g, ' ').trim();
            return txt || 'الكل';
        }

        function openModalFailures() {
            document.getElementById('failures-filter-branch').textContent = getFilterLabel('branch');
            document.getElementById('failures-filter-major').textContent = getFilterLabel('major');
            document.getElementById('failures-filter-gender').textContent = getFilterLabel('gender');
            document.getElementById('failures-filter-year').textContent = getFilterLabel('year');
            document.getElementById('failures-filter-round').textContent = getFilterLabel('round');
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModalFailures() {
            modal.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
        }

        link.addEventListener('click', function (e) {
            e.preventDefault();
            openModalFailures();
        });

        closeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                closeModalFailures();
            });
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModalFailures();
            }
        });

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                var base = window.STUDENTS_DELETE_FAILED_URL || '/students/failures';
                var params = new URLSearchParams();
                if (filterForm) {
                    ['branch', 'major', 'gender', 'year', 'round'].forEach(function (name) {
                        var el = filterForm.querySelector('select[name="' + name + '"]');
                        if (el) params.set(name, el.value);
                    });
                }

                var href = base + (params.toString() ? '?' + params.toString() : '');
                var csrf = window.STUDENTS_CSRF_TOKEN || (document.querySelector('meta[name=\"csrf-token\"]') && document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')) || '';

                fetch(href, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        closeModalFailures();
                        var deleted = data && typeof data.deleted === 'number' ? data.deleted : 0;
                        if (deleted > 0) {
                            alert('تم حذف ' + deleted + ' من الطلبة الراسبين/المعيدين بنجاح.');
                        } else {
                            alert('لم يتم العثور على طلبة راسبين/معيدين للفلاتر المحددة.');
                        }
                        window.location.reload();
                    })
                    .catch(function () {
                        closeModalFailures();
                        alert('تعذر تنفيذ عملية الحذف. يرجى المحاولة مرة أخرى.');
                    });
            });
        }
    })();
})();
