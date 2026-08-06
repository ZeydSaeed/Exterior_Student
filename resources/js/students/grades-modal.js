/**
 * مودال درجات الطالب — منطق الفتح/الإغلاق/التعديل دون innerHTML (تقليل XSS)
 */
(function () {
    function init() {
        const modal = document.getElementById('grades-modal');
        const form = document.getElementById('grades-form');
        if (!modal || !form) return;

        const btnEdit = document.getElementById('grades-btn-edit');
        const btnSave = document.getElementById('grades-btn-save');
        const btnCancel = document.getElementById('grades-btn-cancel');
        const openButtons = document.querySelectorAll('.btn-grades-open');
        const closeBtn = document.querySelector('[data-grades-modal-close]');
        const tbody = document.getElementById('grades-tbody');

        let currentData = null;
        /** نسخة من البيانات عند فتح المودال لاستعادتها عند الإلغاء */
        let originalData = null;

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
                if (edit && !isAutoSum) el.removeAttribute('readonly'); else el.setAttribute('readonly', 'readonly');
            });
            document.querySelectorAll('.grades-row-score').forEach(function (el) {
                el.readOnly = !edit;
                if (edit) el.removeAttribute('readonly'); else el.setAttribute('readonly', 'readonly');
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
            setInputValue('grades-total-input', totalStr);
        }

        function setText(id, val) {
            const el = document.getElementById(id);
            if (el) el.textContent = val != null ? String(val) : '';
        }

        function setInputValue(id, val) {
            const el = document.getElementById(id);
            if (el) el.value = val != null ? String(val) : '';
        }

        /** بناء صف درجات دون innerHTML (آمن من XSS) */
        function appendGradeRow(index, subject, score) {
            const tr = document.createElement('tr');

            const tdSubject = document.createElement('td');
            const inputSubject = document.createElement('input');
            inputSubject.type = 'text';
            inputSubject.className = 'grades-row-subject';
            inputSubject.name = 'grades[' + index + '][subject]';
            inputSubject.value = subject != null ? String(subject) : '';
            inputSubject.readOnly = true;
            tdSubject.appendChild(inputSubject);
            tr.appendChild(tdSubject);

            const tdScore = document.createElement('td');
            const inputScore = document.createElement('input');
            inputScore.type = 'text';
            inputScore.className = 'grades-row-score';
            inputScore.name = 'grades[' + index + '][score]';
            inputScore.value = score != null ? String(score) : '';
            inputScore.readOnly = true;
            inputScore.addEventListener('input', recalculateTotal);
            inputScore.addEventListener('change', recalculateTotal);
            tdScore.appendChild(inputScore);
            tr.appendChild(tdScore);

            tbody.appendChild(tr);
        }

        function fillForm(data) {
            currentData = data;
            originalData = data && typeof data === 'object' ? JSON.parse(JSON.stringify(data)) : null;
            const sid = document.getElementById('grades-student-id');
            if (sid) sid.value = data.id || '';

            setText('grades-exam-number', data.exam_number);
            setInputValue('grades-exam-number-input', data.exam_number);
            setText('grades-name-student', data.name_student != null ? data.name_student : (data.full_name || ''));
            setInputValue('grades-name-student-input', data.name_student != null ? data.name_student : (data.full_name || ''));
            setText('grades-name-father', data.name_father != null ? data.name_father : '');
            setInputValue('grades-name-father-input', data.name_father != null ? data.name_father : '');
            setText('grades-name-grandfather', data.name_grandfather != null ? data.name_grandfather : '');
            setInputValue('grades-name-grandfather-input', data.name_grandfather != null ? data.name_grandfather : '');
            setText('grades-name-surname', data.name_surname != null ? data.name_surname : '');
            setInputValue('grades-name-surname-input', data.name_surname != null ? data.name_surname : '');
            setText('grades-birth-date', formatDisplayDate(data.birth_date));
            setInputValue('grades-birth-date-input', data.birth_date != null ? data.birth_date : '');
            setText('grades-birth-place', data.birth_place != null ? data.birth_place : '');
            setInputValue('grades-birth-place-input', data.birth_place != null ? data.birth_place : '');
            setText('grades-mother-full-name', data.mother_full_name != null ? data.mother_full_name : '');
            setInputValue('grades-mother-full-name-input', data.mother_full_name != null ? data.mother_full_name : '');
            setText('grades-gender', data.gender != null ? data.gender : '');
            setInputValue('grades-gender-input', data.gender != null ? data.gender : '');
            setText('grades-branch', data.branch);
            setInputValue('grades-branch-input', data.branch);
            setText('grades-major', data.major);
            setInputValue('grades-major-input', data.major);
            setText('grades-year', data.academic_year);
            setInputValue('grades-year-input', data.academic_year);
            setText('grades-average', data.average);
            setInputValue('grades-average-input', data.average);
            setText('grades-result', data.result);
            setInputValue('grades-result-input', data.result);
            setText('grades-round', data.round);
            setInputValue('grades-round-input', data.round);

            tbody.innerHTML = '';
            const grades = data.grades || [];
            if (grades.length === 0) {
                appendGradeRow(0, '', '');
                appendGradeRow(1, '', '');
                appendGradeRow(2, '', '');
            } else {
                grades.forEach(function (row, i) {
                    appendGradeRow(i, row.subject, row.score);
                });
            }
            recalculateTotal();
            setEditMode(false);
        }

        /**
         * تحميل بيانات الدرجات وفتح المودال، مع إمكانية الفتح مباشرة في وضع التعديل.
         *
         * @param {string|number} id
         * @param {HTMLElement} sourceBtn
         * @param {boolean} editImmediately
         */
        function openGradesFor(id, sourceBtn, editImmediately) {
            if (!id) return;
            openModal();
            const urlTemplate = window.STUDENTS_GRADES_URL_TEMPLATE || '/students/__ID__/grades';
            const url = urlTemplate.replace('__ID__', id);
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) return;
                    fillForm(data);
                    if (editImmediately) setEditMode(true);
                })
                .catch(function () {
                    // في حال الفشل، استخدم البيانات المتاحة من زر الجدول
                    const btn = sourceBtn;
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
                        grades: [
                            { subject: '', score: '' },
                            { subject: '', score: '' },
                            { subject: '', score: '' }
                        ]
                    });
                    if (editImmediately) setEditMode(true);
                });
        }

        // زر "الدرجات" يفتح المودال في وضع العرض
        openButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-student-id');
                openGradesFor(id, this, false);
            });
        });

        // زر "تعديل" في عمود الإجراءات يفتح نفس المودال لكن مباشرة في وضع التعديل
        document.querySelectorAll('.btn-edit-row').forEach(function (btnEditRow) {
            btnEditRow.addEventListener('click', function () {
                const row = this.closest('tr');
                if (!row) return;
                const gradesBtn = row.querySelector('.btn-grades-open');
                if (!gradesBtn) return;
                const id = gradesBtn.getAttribute('data-student-id');
                openGradesFor(id, gradesBtn, true);
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        if (btnEdit) btnEdit.addEventListener('click', function () { setEditMode(true); });
        if (btnCancel) btnCancel.addEventListener('click', function () {
            if (originalData) fillForm(originalData);
            else setEditMode(false);
        });

        /** تحديث صف الجدول بعد الحفظ الناجح (الاسم، الرقم الامتحاني، إلخ) */
        function updateTableRow(studentId, payload) {
            var btn = document.querySelector('.btn-grades-open[data-student-id="' + studentId + '"]');
            if (!btn) return;
            var row = btn.closest('tr');
            if (!row || !row.cells || row.cells.length < 3) return;
            var fullName = [payload.name_student, payload.name_father, payload.name_grandfather, payload.name_surname].filter(Boolean).join(' ').trim() || payload.name_student || '';
            var examWestern = (function (value) {
                if (window.ArabicDate && typeof window.ArabicDate.toWesternDigits === 'function') {
                    return window.ArabicDate.toWesternDigits(value);
                }
                var s = value == null ? '' : String(value);
                var arabic = '٠١٢٣٤٥٦٧٨٩';
                var out = '';
                for (var i = 0; i < s.length; i++) {
                    var c = s.charAt(i);
                    var idx = arabic.indexOf(c);
                    out += idx >= 0 ? String(idx) : c;
                }
                return out;
            })(payload.exam_number != null ? payload.exam_number : '');
            row.cells[1].textContent = examWestern;
            row.cells[2].textContent = fullName;
            if (row.setAttribute) {
                row.setAttribute('data-name', fullName);
                row.setAttribute('data-exam', examWestern);
            }
            btn.setAttribute('data-name', fullName);
            btn.setAttribute('data-exam-number', examWestern);
            btn.setAttribute('data-gender', payload.gender != null ? String(payload.gender) : '');
            btn.setAttribute('data-branch', payload.branch != null ? String(payload.branch) : '');
            btn.setAttribute('data-major', payload.major != null ? String(payload.major) : '');
            btn.setAttribute('data-year', payload.academic_year != null ? String(payload.academic_year) : '');
            btn.setAttribute('data-result', payload.result != null ? String(payload.result) : '');
        }

        if (btnSave) btnSave.addEventListener('click', function () {
            if (!currentData || !form) return;
            recalculateTotal();
            const grades = [];
            tbody.querySelectorAll('tr').forEach(function (tr) {
                const subInput = tr.querySelector('.grades-row-subject');
                const scoreInput = tr.querySelector('.grades-row-score');
                grades.push({
                    subject: subInput ? subInput.value : '',
                    score: scoreInput ? scoreInput.value : ''
                });
            });
            const payload = {
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
            const examNumber = String(payload.exam_number || '').trim();
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
            const updateUrl = (window.STUDENTS_GRADES_UPDATE_URL_TEMPLATE || '/students/__ID__/grades').replace('__ID__', String(currentData.id));
            const csrf = window.STUDENTS_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(updateUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
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
                    if (err && err.status === 419) {
                        msg = 'انتهت الجلسة. يرجى تحديث الصفحة والمحاولة مرة أخرى.';
                    } else if (err && err.status === 404) {
                        msg = 'لم يتم العثور على سجل الطالب.';
                    } else if (err && err.body) {
                        try {
                            var data = JSON.parse(err.body);
                            if (data.message) msg = data.message;
                        } catch (e) { /* keep default */ }
                    }
                    if (err && err.status) console.error('Save grades error:', err.status, err.body);
                    alert(msg);
                });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
