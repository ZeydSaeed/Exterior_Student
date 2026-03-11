<aside class="students-filter-sidebar" aria-label="فلاتر البحث">
    <form method="GET" action="{{ $students_filter_form_action ?? route('students.index') }}" id="students-filter-form" class="students-filter-form">
        @if(request()->filled('search'))
            <input type="hidden" name="search" value="{{ request('search') }}" id="students-filter-search-hidden">
        @endif
        <div class="students-filter-cards">
            <div class="students-filter">
                <div class="students-filter-card-options" style="display:flex; justify-content:flex-end; margin-bottom:0; margin-top:0.5rem;">
                    <button type="button" id="students-filter-clear-all" class="btn-primary" style="margin:0;">
                        إلغاء الكل
                    </button>
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الفرع</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="branch" value="" {{ trim((string) request('branch', '')) === '' ? 'checked' : '' }} class="students-filter-radio"> الكل</label>
                    @foreach($branches ?? [] as $b)
                        <label><input type="radio" name="branch" value="{{ $b }}" {{ trim((string) request('branch', '')) !== '' && request('branch') === $b ? 'checked' : '' }} class="students-filter-radio"> {{ $b }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الاختصاص</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="major" value="" {{ trim((string) request('major', '')) === '' ? 'checked' : '' }} class="students-filter-radio"> الكل</label>
                    @foreach($majors ?? [] as $m)
                        <label><input type="radio" name="major" value="{{ $m }}" {{ trim((string) request('major', '')) !== '' && request('major') === $m ? 'checked' : '' }} class="students-filter-radio"> {{ $m }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الجنس</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="gender" value="" {{ trim((string) request('gender', '')) === '' ? 'checked' : '' }} class="students-filter-radio"> الكل</label>
                    @foreach($genders ?? [] as $g)
                        <label><input type="radio" name="gender" value="{{ $g }}" {{ trim((string) request('gender', '')) !== '' && request('gender') === $g ? 'checked' : '' }} class="students-filter-radio"> {{ $g }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">العام الدراسي</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="year" value="" {{ trim((string) request('year', '')) === '' ? 'checked' : '' }} class="students-filter-radio"> الكل</label>
                    @foreach($academicYears ?? [] as $year)
                        <label><input type="radio" name="year" value="{{ $year }}" {{ trim((string) request('year', '')) !== '' && request('year') === $year ? 'checked' : '' }} class="students-filter-radio"> {{ $year }}</label>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
    <script>
        (function () {
            var form = document.getElementById('students-filter-form');
            if (!form) return;
            var action = form.getAttribute('action') || '';
            function currentParams() {
                var params = new URLSearchParams();
                /* إرسال كل المفاتيح الأربعة دائماً: «الكل» = قيمة فارغة تُزيل الفلتر من الجلسة */
                ['branch', 'major', 'gender', 'year'].forEach(function (name) {
                    var el = form.querySelector('input[name="' + name + '"]:checked');
                    if (el) params.set(name, el.value);
                });
                var searchHidden = document.getElementById('students-filter-search-hidden');
                if (searchHidden && searchHidden.value) params.set('search', searchHidden.value);
                return params;
            }
            function applyFilters() {
                var q = currentParams().toString();
                window.location = action + (q ? '?' + q : '');
            }
            form.querySelectorAll('.students-filter-radio').forEach(function (radio) {
                radio.addEventListener('change', function () { applyFilters(); });
            });
            var clearBtn = document.getElementById('students-filter-clear-all');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    ['branch', 'major', 'gender', 'year'].forEach(function (name) {
                        var firstAll = form.querySelector('input[name="' + name + '"][value=""]');
                        if (firstAll) {
                            firstAll.checked = true;
                        }
                    });
                    applyFilters();
                });
            }
        })();
    </script>
</aside>
