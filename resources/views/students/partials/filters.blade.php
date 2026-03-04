<aside class="students-filter-sidebar" aria-label="فلاتر البحث">
    <form method="GET" action="{{ route('students.index') }}" id="students-filter-form" class="students-filter-form">
        @if(request()->filled('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
        <div class="students-filter-cards">
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الفرع</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="branch" value="" {{ !request('branch') ? 'checked' : '' }} onchange="this.form.submit()"> الكل</label>
                    @foreach($branches ?? [] as $b)
                        <label><input type="radio" name="branch" value="{{ $b }}" {{ request('branch') == $b ? 'checked' : '' }} onchange="this.form.submit()"> {{ $b }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الاختصاص</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="major" value="" {{ !request('major') ? 'checked' : '' }} onchange="this.form.submit()"> الكل</label>
                    @foreach($majors ?? [] as $m)
                        <label><input type="radio" name="major" value="{{ $m }}" {{ request('major') == $m ? 'checked' : '' }} onchange="this.form.submit()"> {{ $m }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">الجنس</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="gender" value="" {{ !request('gender') ? 'checked' : '' }} onchange="this.form.submit()"> الكل</label>
                    @foreach($genders ?? [] as $g)
                        <label><input type="radio" name="gender" value="{{ $g }}" {{ request('gender') == $g ? 'checked' : '' }} onchange="this.form.submit()"> {{ $g }}</label>
                    @endforeach
                </div>
            </div>
            <div class="students-filter-card">
                <h3 class="students-filter-card-title">العام الدراسي</h3>
                <div class="students-filter-card-options">
                    <label><input type="radio" name="year" value="" {{ !request('year') ? 'checked' : '' }} onchange="this.form.submit()"> الكل</label>
                    @foreach($academicYears ?? [] as $year)
                        <label><input type="radio" name="year" value="{{ $year }}" {{ request('year') == $year ? 'checked' : '' }} onchange="this.form.submit()"> {{ $year }}</label>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
</aside>
