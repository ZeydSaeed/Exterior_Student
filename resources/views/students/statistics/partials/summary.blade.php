<?php

use App\Support\ArabicDigits;

$allLabel = 'الكل';
$selected = $selectedFilters ?? [];
$branchLabel = trim((string) ($selected['branch'] ?? '')) !== '' ? $selected['branch'] : $allLabel;
$majorLabel = trim((string) ($selected['major'] ?? '')) !== '' ? $selected['major'] : $allLabel;
$yearLabel = trim((string) ($selected['year'] ?? '')) !== '' ? $selected['year'] : $allLabel;
$genderLabel = trim((string) ($selected['gender'] ?? '')) !== '' ? $selected['gender'] : $allLabel;
$roundLabel = trim((string) ($selected['round'] ?? '')) !== '' ? $selected['round'] : $allLabel;
$resultLabel = trim((string) ($selected['result'] ?? '')) !== '' ? $selected['result'] : $allLabel;
$countDisplay = ArabicDigits::toWestern((string) ($totalStudents ?? 0));
?>

<section class="students-statistics-panel" aria-label="إحصائيات الطلبة">
    <header class="students-statistics-header">
        <h1>الاحصائيات</h1>
        <p class="students-statistics-count">
            عدد الطلبة:
            <strong>{{ $countDisplay }}</strong>
        </p>
    </header>

    <div class="students-statistics-filters-summary" aria-label="الفلاتر المختارة">
        <div class="students-statistics-filter-item">
            <span class="students-statistics-filter-key">الفرع</span>
            <span class="students-statistics-filter-value">{{ $branchLabel }}</span>
        </div>
        <div class="students-statistics-filter-item">
            <span class="students-statistics-filter-key">الاختصاص</span>
            <span class="students-statistics-filter-value">{{ $majorLabel }}</span>
        </div>
        <div class="students-statistics-filter-item">
            <span class="students-statistics-filter-key">العام الدراسي</span>
            <span class="students-statistics-filter-value">{{ $yearLabel }}</span>
        </div>
        <div class="students-statistics-filter-item">
            <span class="students-statistics-filter-key">الجنس</span>
            <span class="students-statistics-filter-value">{{ $genderLabel }}</span>
        </div>
        <div class="students-statistics-filter-item">
            <span class="students-statistics-filter-key">الدور</span>
            <span class="students-statistics-filter-value">{{ $roundLabel }}</span>
        </div>
        <div class="students-statistics-filter-item">
            <span class="students-statistics-filter-key">النتيجة</span>
            <span class="students-statistics-filter-value">{{ $resultLabel }}</span>
        </div>
    </div>
</section>
