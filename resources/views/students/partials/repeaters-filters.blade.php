@php
    use App\Support\GenderFilterVariants;
    use App\Support\StudentBranchMajors;
    $filterBranchVal = trim((string) request('branch', ''));
    $filterMajorsForBranch = StudentBranchMajors::majorsForBranch($filterBranchVal !== '' ? $filterBranchVal : null);
    $reqMajorVal = trim((string) request('major', ''));
    if ($filterBranchVal !== '' && $reqMajorVal !== '' && ! in_array($reqMajorVal, $filterMajorsForBranch, true)) {
        $reqMajorVal = '';
    }
    $filterGenderVal = GenderFilterVariants::displayLabel(trim((string) request('gender', '')));
@endphp
<aside class="students-filter-sidebar" aria-label="فلاتر المعيدين">
    <script type="application/json" id="repeaters-branch-majors-json">@json(StudentBranchMajors::byBranch())</script>
    <form method="GET" action="{{ route('students.repeaters.index') }}" id="repeaters-filter-form" class="students-filter-form">
        @if(request()->filled('search'))
            <input type="hidden" name="search" value="{{ request('search') }}" id="repeaters-filter-search-hidden">
        @endif
        <div class="students-filter-cards">
            <div class="students-filter">
                <div class="students-filter-card-options" style="display:flex; justify-content:flex-end; margin-bottom:0; margin-top:0.5rem;">
                    <button type="button" id="repeaters-filter-clear-all" class="btn-primary" style="margin:0;">
                        إلغاء الكل
                    </button>
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الفرع</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="branch" value="" {{ $filterBranchVal === '' ? 'checked' : '' }} class="repeaters-filter-radio"> الكل</label>
                    @foreach($branches ?? [] as $b)
                        <label><input type="radio" name="branch" value="{{ $b }}" {{ $filterBranchVal !== '' && request('branch') === $b ? 'checked' : '' }} class="repeaters-filter-radio"> {{ $b }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الاختصاص</h3>
                <div class="students-filter-card-options" id="repeaters-filter-major-options">
                    <label><input type="radio" name="major" value="" {{ $reqMajorVal === '' ? 'checked' : '' }} class="repeaters-filter-radio"> الكل</label>
                    @foreach($filterMajorsForBranch as $m)
                        <label><input type="radio" name="major" value="{{ $m }}" {{ $reqMajorVal !== '' && $reqMajorVal === $m ? 'checked' : '' }} class="repeaters-filter-radio"> {{ $m }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الجنس</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="gender" value="" {{ $filterGenderVal === '' ? 'checked' : '' }} class="repeaters-filter-radio"> الكل</label>
                    @foreach($genders ?? [] as $g)
                        <label><input type="radio" name="gender" value="{{ $g }}" {{ $filterGenderVal !== '' && $filterGenderVal === $g ? 'checked' : '' }} class="repeaters-filter-radio"> {{ $g }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">العام الدراسي (مطلوب)</h3>
                <div class="students-filter-card-options">
                    @foreach($academicYears ?? [] as $year)
                        <label><input type="radio" name="year" value="{{ $year }}" {{ request('year') === $year ? 'checked' : '' }} class="repeaters-filter-radio"> {{ $year }}</label>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
    <script>
        (function () {
            var form = document.getElementById('repeaters-filter-form');
            if (!form) return;
            var action = form.getAttribute('action') || '';

            function parseBranchMajorsMap() {
                var el = document.getElementById('repeaters-branch-majors-json');
                if (!el || !el.textContent) return {};
                try {
                    return JSON.parse(el.textContent);
                } catch (e) {
                    return {};
                }
            }

            var branchMajorsMap = parseBranchMajorsMap();

            function syncMajorRadiosFromBranch() {
                var container = document.getElementById('repeaters-filter-major-options');
                if (!container) return;
                var branchEl = form.querySelector('input[name="branch"]:checked');
                var branch = branchEl ? branchEl.value : '';
                var prevMajor = '';
                var prevChecked = form.querySelector('input[name="major"]:checked');
                if (prevChecked) prevMajor = prevChecked.value;

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

                container.innerHTML = '';
                function addMajorRadio(value, labelText, checked) {
                    var label = document.createElement('label');
                    var radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'major';
                    radio.value = value;
                    radio.className = 'repeaters-filter-radio';
                    if (checked) radio.checked = true;
                    label.appendChild(radio);
                    label.appendChild(document.createTextNode(' ' + labelText));
                    container.appendChild(label);
                }

                addMajorRadio('', 'الكل', !keepMajor);
                majors.forEach(function (m) {
                    addMajorRadio(m, m, keepMajor && prevMajor === m);
                });
            }

            function currentParams() {
                var params = new URLSearchParams();
                ['branch', 'major', 'gender', 'year'].forEach(function (name) {
                    var el = form.querySelector('input[name="' + name + '"]:checked');
                    if (el) params.set(name, el.value);
                });
                var searchHidden = document.getElementById('repeaters-filter-search-hidden');
                if (searchHidden && searchHidden.value) params.set('search', searchHidden.value);
                return params;
            }
            function applyFilters() {
                var yearEl = form.querySelector('input[name="year"]:checked');
                if (!yearEl || !yearEl.value) {
                    alert('العام الدراسي مطلوب.');
                    return;
                }
                var q = currentParams().toString();
                window.location = action + (q ? '?' + q : '');
            }
            form.addEventListener('change', function (e) {
                var t = e.target;
                if (!t || t.tagName !== 'INPUT' || t.type !== 'radio' || !t.classList.contains('repeaters-filter-radio')) return;
                if (t.name === 'branch') {
                    syncMajorRadiosFromBranch();
                }
                applyFilters();
            });
            var clearBtn = document.getElementById('repeaters-filter-clear-all');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    ['branch', 'major', 'gender'].forEach(function (name) {
                        var firstAll = form.querySelector('input[name="' + name + '"][value=""]');
                        if (firstAll) {
                            firstAll.checked = true;
                        }
                    });
                    syncMajorRadiosFromBranch();
                    applyFilters();
                });
            }
        })();
    </script>
</aside>
