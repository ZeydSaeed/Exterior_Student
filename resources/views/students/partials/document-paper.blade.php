{{-- جزء ورقة القيد فقط — يُضمَّن لكل طالب في الطباعة الدفعية --}}
@php
    $ad = fn ($v) => \App\Support\ArabicDigits::toArabic($v ?? '');
    $emp0 = $dto->employees[0] ?? ['type' => 'مدير قسم التعليم المهني', 'name' => 'غير محدد'];
    $emp1 = $dto->employees[1] ?? ['type' => 'الموظف المسؤول', 'name' => 'غير محدد'];
@endphp
<div class="student-document-paper">
    <div class="doc-header">
        <div class="doc-title">سجل قيد الطلاب الخارجيين للدراسة المهنية العامة</div>
        <div class="doc-header-right">
            <div class="doc-org">وزارة التربية</div>
            <div class="doc-org">المديرية العامة للتعليم المهني</div>
            <div class="doc-org">قسم التعليم المهني في محافظة كربلاء المقدسة</div>
        </div>
        <div class="doc-photo">صورة</div>
    </div>

    <div class="doc-row">
        <span class="doc-label">رقم الصفحة</span>
        <span class="doc-value doc-value-page-number">&nbsp;</span>
    </div>
    <div class="doc-row">
        <span class="doc-label">الرقم الامتحاني</span>
        <span class="doc-value">{{ $ad($dto->examNumber) }}</span>
    </div>
    <div class="doc-row">
        <span class="doc-label">الاسم الرباعي</span>
        <span class="doc-value">{{ $dto->fullName }}</span>
    </div>
    <div class="doc-row doc-row-two">
        <span class="doc-label doc-w-17">التولد</span>
        <span class="doc-value doc-w-18">{{ $ad($dto->birthDate) }}</span>
        <span class="doc-label">محل الولادة</span>
        <span class="doc-value">{{ $ad($dto->birthPlace) }}</span>
    </div>
    <div class="doc-row">
        <span class="doc-label">اسم الام الكامل</span>
        <span class="doc-value">{{ $ad($dto->motherName) }}</span>
    </div>
    <div class="doc-row doc-row-two">
        <span class="doc-label doc-w-11">الفرع</span>
        <span class="doc-value doc-w-13">{{ $ad($dto->branch) }}</span>
        <span class="doc-label doc-w-16">الاختصاص</span>
        <span class="doc-value">{{ $ad($dto->specialization) }}</span>
    </div>
    <div class="doc-row doc-row-wide">
        <span class="doc-label">آخر مدرسة كان فيها الطالب</span>
        <span class="doc-value">{{ $ad($dto->lastSchool) }}</span>
    </div>
    <div class="doc-row doc-row-three">
        <span class="doc-label doc-w-11">رقم وثيقة المتوسطة</span>
        <span class="doc-value doc-w-13">{{ $ad($dto->middleDocNumber) }}</span>
        <span class="doc-label doc-w-14">تاريخها</span>
        <span class="doc-value">{{ $ad($dto->middleDocDate) }}</span>
    </div>
    <div class="doc-row">
        <span class="doc-label">جهة الإصدار</span>
        <span class="doc-value doc-w-15">{{ $ad($dto->issuingAuthority) }}</span>
    </div>
    <div class="doc-row doc-row-three">
        <span class="doc-label doc-w-11">العام الدراسي</span>
        <span class="doc-value">{{ $ad($dto->academicYear) }}</span>
        <span class="doc-label">الدور</span>
        <span class="doc-value">{{ $ad($dto->round) }}</span>
        <span class="doc-label">النتيجة</span>
        <span class="doc-value">{{ $ad($dto->result) }}</span>
    </div>

    <table class="doc-table doc-grades-table" aria-label="جدول الدرجات">
        <thead>
            <tr>
                <th>المادة</th>
                <th>الدرجة رقماً</th>
                <th>الدرجة كتابةً</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dto->gradesTable as $row)
                <tr>
                    <td class="doc-grade-subject">{{ $row['subject'] }}</td>
                    <td class="doc-grade-num">{{ $ad($row['score']) }}</td>
                    <td class="doc-grade-words">{{ $row['score_words'] }}</td>
                </tr>
            @endforeach
            <tr class="doc-total-row">
                <td>المجموع</td>
                <td class="doc-grade-num">{{ $ad($dto->total) }}</td>
                <td class="doc-grade-words">{{ $dto->totalWords }}</td>
            </tr>
        </tbody>
    </table>

    @if(count($dto->subjectsCompleted) > 0)
        <div class="doc-completed-row">
            <span class="doc-label doc-label-block">الدروس التي أكمل بها: {{ $ad(implode('، ', $dto->subjectsCompleted)) }}</span>
        </div>
    @endif

    <div class="doc-section-title">الوثائق التي زود بها:</div>
    <table class="doc-table doc-docs-table" aria-label="جدول الوثائق">
        <thead>
            <tr>
                <th>رقم الوثيقة</th>
                <th>تاريخها</th>
                <th>الجهة المعنون إليها</th>
                <th>الغرض من الوثيقة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dto->documents as $rec)
                <tr>
                    <td>{{ $ad($rec->documentNumber) }}</td>
                    <td>{{ $ad($rec->documentDate) }}</td>
                    <td>{{ $ad($rec->addressee) }}</td>
                    <td>{{ $ad($rec->purpose) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">لا توجد وثائق لهذا الطالب حالياً.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="doc-signatures">
        <div class="doc-sig-cell">
            <div class="doc-sig-title">{{ $emp0['type'] }}</div>
            <div class="doc-sig-name">{{ $emp0['name'] }}</div>
        </div>
        <div class="doc-sig-cell">
            <div class="doc-sig-title">{{ $emp1['type'] }}</div>
            <div class="doc-sig-name">{{ $emp1['name'] }}</div>
        </div>
    </div>
</div>
