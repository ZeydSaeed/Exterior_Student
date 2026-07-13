@php
    use App\Support\StudentBranchMajors;
    use App\Support\StudentListFiltersSession;

    $useSessionMerge = $useStudentListSessionMerge ?? true;
    $merged = $useSessionMerge
        ? StudentListFiltersSession::mergeRequestWithSession(request())
        : array_filter(
            [
                'branch' => (string) request('branch', ''),
                'major' => (string) request('major', ''),
                'gender' => (string) request('gender', ''),
                'year' => (string) request('year', ''),
                'round' => (string) request('round', ''),
                'result' => (string) request('result', ''),
            ],
            static fn ($v) => $v !== ''
        );

    $filterBranchVal = trim((string) ($merged['branch'] ?? ''));
    $filterMajorsForBranch = StudentBranchMajors::majorsForBranch($filterBranchVal !== '' ? $filterBranchVal : null);
    $reqMajorVal = trim((string) ($merged['major'] ?? ''));
    if ($filterBranchVal !== '' && $reqMajorVal !== '' && ! in_array($reqMajorVal, $filterMajorsForBranch, true)) {
        $reqMajorVal = '';
    }
    $filterGenderVal = trim((string) ($merged['gender'] ?? ''));
    $filterYearVal = trim((string) ($merged['year'] ?? ''));
    $filterRoundVal = trim((string) ($merged['round'] ?? ''));
    $filterResultVal = trim((string) ($merged['result'] ?? ''));
@endphp
<aside class="students-filter-sidebar" aria-label="فلاتر البحث">
    <script type="application/json" id="student-branch-majors-json">@json(StudentBranchMajors::byBranch())</script>
    <form method="GET" action="{{ $students_filter_form_action ?? route('students.index') }}" id="students-filter-form" class="students-filter-form">
        <div class="students-filter-cards">
            <div class="students-filter">
                <div class="students-filter-card-options students-filter-clear-row">
                    <button type="button" id="students-filter-clear-all" class="btn-primary">
                        إلغاء الكل
                    </button>
                </div>
            </div>
            <div class="students-filter-card">
                <label class="students-filter-card-title" for="students-filter-branch">الفرع</label>
                <select name="branch" id="students-filter-branch" class="students-filter-select students-filter-control" aria-label="الفرع">
                    <option value="" @selected($filterBranchVal === '')>الكل</option>
                    @foreach($branches ?? [] as $b)
                        <option value="{{ $b }}" @selected($filterBranchVal === $b)>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div class="students-filter-card">
                <label class="students-filter-card-title" for="students-filter-major">الاختصاص</label>
                <select name="major" id="students-filter-major" class="students-filter-select students-filter-control" aria-label="الاختصاص">
                    <option value="" @selected($reqMajorVal === '')>الكل</option>
                    @foreach($filterMajorsForBranch as $m)
                        <option value="{{ $m }}" @selected($reqMajorVal === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="students-filter-card">
                <label class="students-filter-card-title" for="students-filter-gender">الجنس</label>
                <select name="gender" id="students-filter-gender" class="students-filter-select students-filter-control" aria-label="الجنس">
                    <option value="" @selected($filterGenderVal === '')>الكل</option>
                    @foreach($genders ?? [] as $g)
                        <option value="{{ $g }}" @selected($filterGenderVal === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="students-filter-card">
                <label class="students-filter-card-title" for="students-filter-year">العام الدراسي</label>
                <select name="year" id="students-filter-year" class="students-filter-select students-filter-control" aria-label="العام الدراسي">
                    <option value="" @selected($filterYearVal === '')>الكل</option>
                    @foreach($academicYears ?? [] as $year)
                        <option value="{{ $year }}" @selected($filterYearVal === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="students-filter-card">
                <label class="students-filter-card-title" for="students-filter-round">الدور</label>
                <select name="round" id="students-filter-round" class="students-filter-select students-filter-control" aria-label="الدور">
                    <option value="" @selected($filterRoundVal === '')>الكل</option>
                    @foreach($roundOptions ?? [] as $round)
                        <option value="{{ $round }}" @selected($filterRoundVal === $round)>{{ $round }}</option>
                    @endforeach
                </select>
            </div>
            <div class="students-filter-card">
                <label class="students-filter-card-title" for="students-filter-result">النتيجة</label>
                <select name="result" id="students-filter-result" class="students-filter-select students-filter-control" aria-label="النتيجة">
                    <option value="" @selected($filterResultVal === '')>الكل</option>
                    @foreach($resultOptions ?? [] as $result)
                        <option value="{{ $result }}" @selected($filterResultVal === $result)>{{ $result }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
    <script>
        (function () {
            var form = document.getElementById('students-filter-form');
            if (!form) return;
            var action = form.getAttribute('action') || '';
            var filterKeys = ['branch', 'major', 'gender', 'year', 'round', 'result'];

            function parseBranchMajorsMap() {
                var el = document.getElementById('student-branch-majors-json');
                if (!el || !el.textContent) return {};
                try {
                    return JSON.parse(el.textContent);
                } catch (e) {
                    return {};
                }
            }

            var branchMajorsMap = parseBranchMajorsMap();

            function syncMajorOptionsFromBranch() {
                var majorSelect = document.getElementById('students-filter-major');
                if (!majorSelect) return;
                var branchSelect = form.querySelector('select[name="branch"]');
                var branch = branchSelect ? branchSelect.value : '';
                var prevMajor = majorSelect.value;

                var majors = [];
                if (!branch) {
                    var seen = {};
                    Object.keys(branchMajorsMap).forEach(function (b) {
                        (branchMajorsMap[b] || []).forEach(function (m) {
                            seen[m] = true;
                        });
                    });
                    majors = Object.keys(seen).sort();
                } else {
                    majors = (branchMajorsMap[branch] || []).slice();
                }

                var keepMajor = prevMajor !== '' && majors.indexOf(prevMajor) !== -1;

                majorSelect.innerHTML = '';
                function addMajorOption(value, labelText, selected) {
                    var opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = labelText;
                    if (selected) opt.selected = true;
                    majorSelect.appendChild(opt);
                }

                addMajorOption('', 'الكل', !keepMajor);
                majors.forEach(function (m) {
                    addMajorOption(m, m, keepMajor && prevMajor === m);
                });
            }

            function currentParams() {
                var params = new URLSearchParams();
                filterKeys.forEach(function (name) {
                    var el = form.querySelector('select[name="' + name + '"]');
                    if (el) params.set(name, el.value);
                });
                return params;
            }

            function applyFilters() {
                var q = currentParams().toString();
                window.location = action + (q ? '?' + q : '');
            }

            form.addEventListener('change', function (e) {
                var t = e.target;
                if (!t || t.tagName !== 'SELECT' || !t.classList.contains('students-filter-control')) return;
                if (t.name === 'branch') {
                    syncMajorOptionsFromBranch();
                }
                applyFilters();
            });

            var clearBtn = document.getElementById('students-filter-clear-all');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    filterKeys.forEach(function (name) {
                        var el = form.querySelector('select[name="' + name + '"]');
                        if (el) el.value = '';
                    });
                    syncMajorOptionsFromBranch();
                    applyFilters();
                });
            }
        })();
    </script>
</aside>
