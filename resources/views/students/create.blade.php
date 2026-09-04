@extends('layouts.dashboard')

@section('title', 'إضافة طالب')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-employees page-add-student')

@section('content')
    <div class="employees-page-wrap">
        {{-- أزرار الحفظ والإغلاق: حفظ أولاً فيصل لليمين في RTL، ثم إغلاق — حفظ بجانب يمين إغلاق --}}
        <div class="employees-page-actions-top">
            <button type="submit" form="form-add-student" class="btn-primary btn-save-top">حفظ</button>
            <a href="{{ route('students.index') }}" class="btn-primary btn-close-top" title="إغلاق">إغلاق</a>
        </div>

        <div class="employees-page-header">
            <h1>إضافة طالب</h1>
        </div>

        @if($errors->any())
            <ul class="employees-error-list">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('students.store') }}" id="form-add-student" class="form-add-student">
            @csrf
            @php
                $branchMap = \App\Support\StudentBranchMajors::byBranch();
                $selectedBranch = old('branch', '');
                $selectedMajor = old('major', '');
                $majorsForSelected = \App\Support\StudentBranchMajors::majorsForBranch($selectedBranch !== '' ? $selectedBranch : null);
                if ($selectedBranch === '') {
                    $majorsForSelected = [];
                }
                $selectedAcademicYear = old('academic_year', \App\Support\AcademicYearOptions::current());
                $selectedGender = old('gender', '');
            @endphp

            <div class="form-fields-align">
            <div class="form-row form-row-1">
                <div class="form-group">
                    <label for="enrollment_number">رقم القيد</label>
                    <input type="text" inputmode="numeric" id="enrollment_number" name="enrollment_number" value="{{ old('enrollment_number') }}" maxlength="50" aria-label="رقم القيد (اختياري)" autofocus />
                </div>
                <div class="form-group">
                    <label for="exam_number">الرقم الامتحاني <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" id="exam_number" name="exam_number" value="{{ old('exam_number') }}" required maxlength="255" aria-label="الرقم الامتحاني (مطلوب)" />
                </div>
                <div class="form-group">
                    <label for="name_student">اسم الطالب <span class="required">*</span></label>
                    <input type="text" id="name_student" name="name_student" value="{{ old('name_student') }}" required maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="name_father">اسم الاب <span class="required">*</span></label>
                    <input type="text" id="name_father" name="name_father" value="{{ old('name_father') }}" required maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="name_grandfather">اسم الجد <span class="required">*</span></label>
                    <input type="text" id="name_grandfather" name="name_grandfather" value="{{ old('name_grandfather') }}" required maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="name_surname">اسم اب الجد <span class="required">*</span></label>
                    <input type="text" id="name_surname" name="name_surname" value="{{ old('name_surname') }}" required maxlength="255" />
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="birth_place">محل الولادة <span class="required">*</span></label>
                    <input type="text" id="birth_place" name="birth_place" value="{{ old('birth_place') }}" required maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="birth_date">التولد <span class="required">*</span></label>
                    <input type="date" id="birth_date" name="birth_date" class="arabic-date-field" lang="ar-IQ" value="{{ old('birth_date') ? \App\Support\ImportDateNormalizer::toYmd(old('birth_date')) : '' }}" required autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="mother_full_name">اسم الام الكامل</label>
                    <input type="text" id="mother_full_name" name="mother_full_name" value="{{ old('mother_full_name') }}" maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="gender">الجنس <span class="required">*</span></label>
                    <select id="gender" name="gender" required aria-label="الجنس">
                        <option value="" @selected($selectedGender === '')>اختر الجنس</option>
                        <option value="ذكر" @selected($selectedGender === 'ذكر')>ذكر</option>
                        <option value="انثى" @selected(in_array($selectedGender, ['انثى', 'أنثى'], true))>انثى</option>
                    </select>
                </div>
            </div>

            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="branch">الفرع <span class="required">*</span></label>
                    <select id="branch" name="branch" required aria-label="الفرع">
                        <option value="" @selected($selectedBranch === '')>اختر الفرع</option>
                        @foreach(array_keys($branchMap) as $branch)
                            <option value="{{ $branch }}" @selected($selectedBranch === $branch)>{{ $branch }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="major">الاختصاص <span class="required">*</span></label>
                    <select id="major" name="major" required aria-label="الاختصاص" data-old="{{ $selectedMajor }}">
                        <option value="">{{ $selectedBranch === '' ? 'اختر الفرع أولاً' : 'اختر الاختصاص' }}</option>
                        @foreach($majorsForSelected as $major)
                            <option value="{{ $major }}" @selected($selectedMajor === $major)>{{ $major }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="academic_year">العام الدراسي <span class="required">*</span></label>
                    <select id="academic_year" name="academic_year" required aria-label="العام الدراسي">
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}" @selected($selectedAcademicYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="last_school">اخر مدرسة كان فيها الطالب</label>
                    <input type="text" id="last_school" name="last_school" value="{{ old('last_school') }}" maxlength="500" />
                </div>
                <div class="form-group">
                    <label for="middle_doc_number">رقم الوثيقة المتوسطة</label>
                    <input type="text" id="middle_doc_number" name="middle_doc_number" value="{{ old('middle_doc_number') }}" maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="middle_doc_date">تاريخها</label>
                    <input type="date" id="middle_doc_date" name="middle_doc_date" class="arabic-date-field" lang="ar-IQ" value="{{ old('middle_doc_date') ? \App\Support\ImportDateNormalizer::toYmd(old('middle_doc_date')) : '' }}" autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="issuing_authority">جهة الاصدار</label>
                    <input type="text" id="issuing_authority" name="issuing_authority" value="{{ old('issuing_authority') }}" maxlength="500" />
                </div>
            </div>
            </div>

            <div class="create-grades-section" aria-label="درجات المواد الدراسية">
                <h2 class="create-grades-title">درجات المواد الدراسية</h2>
                <p class="create-grades-hint" id="create-grades-hint">اختر الفرع والاختصاص لإظهار المواد.</p>
                <div class="create-grades-table-wrap">
                    <table class="create-grades-table" id="create-grades-table">
                        <thead>
                            <tr>
                                <th scope="col">المادة</th>
                                <th scope="col">الدرجة</th>
                            </tr>
                        </thead>
                        <tbody id="create-grades-tbody">
                        </tbody>
                    </table>
                </div>
                <div class="create-grades-total">
                    <span>المجموع:</span>
                    <strong id="create-grades-sum">0</strong>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('styles')
<style>
    .page-add-student .arabic-date-field-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
        width: 100%;
        min-height: 2.15rem;
        direction: rtl;
        box-sizing: border-box;
        border: 1px solid var(--color-dark-accent);
        border-radius: 0.25rem;
        background: #fff;
        overflow: hidden;
    }
    .page-add-student .arabic-date-dmy {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        gap: 0.1rem;
        flex: 1 1 auto;
        min-width: 0;
        padding: 0 0.4rem;
        direction: rtl;
    }
    .page-add-student .arabic-date-part {
        border: 0 !important;
        outline: none;
        background: transparent;
        text-align: center;
        font: inherit;
        color: #200f1b;
        padding: 0 !important;
        margin: 0 !important;
        height: auto !important;
        min-width: 0 !important;
        width: auto !important;
        box-shadow: none !important;
        direction: rtl;
    }
    .page-add-student .arabic-date-day,
    .page-add-student .arabic-date-month {
        width: 1.7rem !important;
    }
    .page-add-student .arabic-date-year {
        width: 2.8rem !important;
    }
    .page-add-student .arabic-date-sep {
        color: #4a545e;
        user-select: none;
        flex: 0 0 auto;
    }
    .page-add-student .arabic-date-field-wrap input[type="date"].arabic-date-field {
        position: relative;
        flex: 0 0 0.85rem;
        width: 0.85rem !important;
        min-width: 0.85rem !important;
        max-width: 0.85rem !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent;
        color: transparent;
        cursor: pointer;
    }
    .page-add-student .arabic-date-field-wrap input[type="date"].arabic-date-field::-webkit-calendar-picker-indicator {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        cursor: pointer;
        transform: scale(0.45);
        transform-origin: center;
        opacity: 0.85;
    }
    .page-add-student .arabic-date-field-wrap input[type="date"].arabic-date-field::-webkit-datetime-edit {
        display: none;
    }
</style>
@endsection

@section('scripts')
<script src="{{ url('js/arabic-date.js') }}?v={{ file_exists(public_path('js/arabic-date.js')) ? filemtime(public_path('js/arabic-date.js')) : time() }}"></script>
<script>
(function () {
    var subjectObject = @json(\App\Support\StudentBranchMajors::subjectObjectForJs());
    var subjectsByBranchMajor = @json($subjectsByBranchMajor ?? []);
    var oldGrades = @json(old('grades', []));
    var branchSelect = document.getElementById('branch');
    var majorSelect = document.getElementById('major');
    var gradesTbody = document.getElementById('create-grades-tbody');
    var gradesHint = document.getElementById('create-grades-hint');
    var gradesSumEl = document.getElementById('create-grades-sum');
    var oldMajor = (majorSelect && majorSelect.getAttribute('data-old')) || '';
    var scoreCache = {};

    if (Array.isArray(oldGrades)) {
        oldGrades.forEach(function (row) {
            if (!row || !row.subject) {
                return;
            }
            scoreCache[String(row.subject)] = row.score != null ? String(row.score) : '';
        });
    }

    function rememberScores() {
        if (!gradesTbody) {
            return;
        }
        gradesTbody.querySelectorAll('tr').forEach(function (tr) {
            var subjectInput = tr.querySelector('input[name$="[subject]"]');
            var scoreInput = tr.querySelector('input[name$="[score]"]');
            if (subjectInput && scoreInput) {
                scoreCache[subjectInput.value] = scoreInput.value;
            }
        });
    }

    function updateSum() {
        if (!gradesTbody || !gradesSumEl) {
            return;
        }
        var sum = 0;
        gradesTbody.querySelectorAll('input[name$="[score]"]').forEach(function (input) {
            var v = String(input.value || '').trim();
            if (v !== '' && !isNaN(Number(v))) {
                sum += Math.round(Number(v));
            }
        });
        gradesSumEl.textContent = String(sum);
    }

    function fillGradesTable() {
        if (!gradesTbody) {
            return;
        }
        rememberScores();
        gradesTbody.innerHTML = '';
        var branch = branchSelect ? (branchSelect.value || '') : '';
        var major = majorSelect ? (majorSelect.value || '') : '';
        var subjects = (branch && major && subjectsByBranchMajor[branch] && subjectsByBranchMajor[branch][major])
            ? subjectsByBranchMajor[branch][major]
            : [];

        if (!subjects.length) {
            if (gradesHint) {
                gradesHint.textContent = 'اختر الفرع والاختصاص لإظهار المواد.';
                gradesHint.hidden = false;
            }
            updateSum();
            return;
        }

        if (gradesHint) {
            gradesHint.hidden = true;
        }

        subjects.forEach(function (subject, index) {
            var tr = document.createElement('tr');
            var tdSubject = document.createElement('td');
            var subjectInput = document.createElement('input');
            subjectInput.type = 'hidden';
            subjectInput.name = 'grades[' + index + '][subject]';
            subjectInput.value = subject;
            tdSubject.appendChild(document.createTextNode(subject));
            tdSubject.appendChild(subjectInput);

            var tdScore = document.createElement('td');
            var scoreInput = document.createElement('input');
            scoreInput.type = 'text';
            scoreInput.name = 'grades[' + index + '][score]';
            scoreInput.className = 'create-grades-score';
            scoreInput.inputMode = 'numeric';
            scoreInput.maxLength = 10;
            scoreInput.setAttribute('aria-label', 'درجة ' + subject);
            scoreInput.value = Object.prototype.hasOwnProperty.call(scoreCache, subject) ? scoreCache[subject] : '';
            scoreInput.addEventListener('input', updateSum);
            tdScore.appendChild(scoreInput);

            tr.appendChild(tdSubject);
            tr.appendChild(tdScore);
            gradesTbody.appendChild(tr);
        });
        updateSum();
    }

    function fillMajorOptions() {
        if (!majorSelect || !branchSelect) {
            return;
        }
        var branch = branchSelect.value || '';
        var previous = majorSelect.value || oldMajor;
        majorSelect.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = branch ? 'اختر الاختصاص' : 'اختر الفرع أولاً';
        majorSelect.appendChild(placeholder);

        if (branch && subjectObject[branch]) {
            var majors = subjectObject[branch];
            for (var key in majors) {
                if (!Object.prototype.hasOwnProperty.call(majors, key)) {
                    continue;
                }
                var option = document.createElement('option');
                option.value = key;
                option.textContent = key;
                if (key === previous) {
                    option.selected = true;
                }
                majorSelect.appendChild(option);
            }
        }
        oldMajor = '';
        fillGradesTable();
    }

    if (branchSelect) {
        branchSelect.addEventListener('change', function () {
            oldMajor = '';
            fillMajorOptions();
        });
    }
    if (majorSelect) {
        majorSelect.addEventListener('change', fillGradesTable);
    }
    fillMajorOptions();

    var formEl = document.getElementById('form-add-student');
    var saveBtn = document.querySelector('.btn-save-top');
    var fieldOrder = [
        'enrollment_number',
        'exam_number',
        'name_student',
        'name_father',
        'name_grandfather',
        'name_surname',
        'birth_place',
        'birth_date',
        'mother_full_name',
        'gender',
        'branch',
        'major',
        'academic_year',
        'last_school',
        'middle_doc_number',
        'middle_doc_date',
        'issuing_authority'
    ];

    function resolveFieldId(el) {
        if (!el) {
            return '';
        }
        if (el.id && fieldOrder.indexOf(el.id) !== -1) {
            return el.id;
        }
        if (el.classList && el.classList.contains('create-grades-score')) {
            return 'grade-score';
        }
        if (el.classList && el.classList.contains('btn-save-top')) {
            return 'save';
        }
        var wrap = el.closest ? el.closest('.form-group, .arabic-date-field-wrap') : null;
        if (wrap) {
            var control = wrap.querySelector('input[id], select[id]');
            if (control && fieldOrder.indexOf(control.id) !== -1) {
                return control.id;
            }
        }
        return el.id || '';
    }

    function focusElement(el) {
        if (!el) {
            return;
        }
        el.focus();
        if (typeof el.select === 'function' && el.tagName === 'INPUT' && el.type !== 'date') {
            try {
                el.select();
            } catch (e) {
                // ignore
            }
        }
    }

    function gradeScoreInputs() {
        return Array.prototype.slice.call(document.querySelectorAll('#create-grades-tbody .create-grades-score'));
    }

    function focusNextFromField(fieldId) {
        var idx = fieldOrder.indexOf(fieldId);
        if (idx === -1) {
            return;
        }
        if (idx < fieldOrder.length - 1) {
            focusElement(document.getElementById(fieldOrder[idx + 1]));
            return;
        }
        var scores = gradeScoreInputs();
        if (scores.length) {
            focusElement(scores[0]);
            return;
        }
        focusElement(saveBtn);
    }

    function focusNextFromGrade(current) {
        var scores = gradeScoreInputs();
        var i = scores.indexOf(current);
        if (i !== -1 && i < scores.length - 1) {
            focusElement(scores[i + 1]);
            return;
        }
        focusElement(saveBtn);
    }

    if (formEl) {
        formEl.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') {
                return;
            }
            var target = e.target;
            if (!target) {
                return;
            }
            if (target.tagName === 'TEXTAREA') {
                return;
            }
            if (target.classList && target.classList.contains('btn-save-top')) {
                return;
            }

            var fieldId = resolveFieldId(target);
            if (fieldId === 'grade-score') {
                e.preventDefault();
                focusNextFromGrade(target);
                return;
            }
            if (fieldOrder.indexOf(fieldId) !== -1) {
                e.preventDefault();
                focusNextFromField(fieldId);
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (formEl) {
                    formEl.requestSubmit ? formEl.requestSubmit(saveBtn) : formEl.submit();
                }
            }
        });
    }
})();
</script>
@endsection
