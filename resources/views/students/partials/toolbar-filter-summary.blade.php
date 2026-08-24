@php
    use App\Support\ResultFilterVariants;
    use App\Support\StudentListFiltersSession;

    if ($useStudentListSessionMerge ?? true) {
        $merged = StudentListFiltersSession::mergeRequestWithSession(request());
        $branch = trim((string) ($merged['branch'] ?? ''));
        $major = trim((string) ($merged['major'] ?? ''));
        $year = trim((string) ($merged['year'] ?? ''));
        $gender = trim((string) ($merged['gender'] ?? ''));
        $round = trim((string) ($merged['round'] ?? ''));
        $result = ResultFilterVariants::resolveFilterOption(trim((string) ($merged['result'] ?? '')));
    } else {
        $branch = trim((string) request('branch', ''));
        $major = trim((string) request('major', ''));
        $year = trim((string) request('year', ''));
        $gender = trim((string) request('gender', ''));
        $round = trim((string) request('round', ''));
        $result = ResultFilterVariants::resolveFilterOption(trim((string) request('result', '')));
    }
    $allLabel = 'الكل';
@endphp
<div class="dashboard-toolbar-filters-summary" aria-label="الفلاتر المفعّلة">
    <span class="dashboard-toolbar-filter-item"><span class="dashboard-toolbar-filter-key">الفرع:</span> {{ $branch !== '' ? $branch : $allLabel }}</span>
    <span class="dashboard-toolbar-filter-sep" aria-hidden="true">|</span>
    <span class="dashboard-toolbar-filter-item"><span class="dashboard-toolbar-filter-key">الاختصاص:</span> {{ $major !== '' ? $major : $allLabel }}</span>
    <span class="dashboard-toolbar-filter-sep" aria-hidden="true">|</span>
    <span class="dashboard-toolbar-filter-item"><span class="dashboard-toolbar-filter-key">العام الدراسي:</span> {{ $year !== '' ? $year : $allLabel }}</span>
    <span class="dashboard-toolbar-filter-sep" aria-hidden="true">|</span>
    <span class="dashboard-toolbar-filter-item"><span class="dashboard-toolbar-filter-key">الجنس:</span> {{ $gender !== '' ? $gender : $allLabel }}</span>
    <span class="dashboard-toolbar-filter-sep" aria-hidden="true">|</span>
    <span class="dashboard-toolbar-filter-item"><span class="dashboard-toolbar-filter-key">الدور:</span> {{ $round !== '' ? $round : $allLabel }}</span>
    <span class="dashboard-toolbar-filter-sep" aria-hidden="true">|</span>
    <span class="dashboard-toolbar-filter-item"><span class="dashboard-toolbar-filter-key">النتيجة:</span> {{ $result !== '' ? $result : $allLabel }}</span>
</div>
