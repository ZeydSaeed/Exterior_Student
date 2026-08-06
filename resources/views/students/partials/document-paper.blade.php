{{-- جزء ورقة القيد فقط — يُضمَّن لكل طالب في الطباعة الدفعية --}}
@php
    $ad = fn ($v) => \App\Support\ArabicDigits::toArabic($v ?? '');
    $dateYmd = static fn ($v) => \App\Support\ImportDateNormalizer::toYmd($v) ?? '';
    $dateDisplay = static fn ($v) => \App\Support\ArabicDigits::toArabic(\App\Support\ImportDateNormalizer::toDisplayDmy($v));
    $cell = static function ($v): string {
        $s = trim((string) ($v ?? ''));

        return $s !== '' ? e($s) : '&nbsp;';
    };
    $emp0 = $dto->employees[0] ?? ['type' => 'مدير قسم التعليم المهني', 'name' => 'غير محدد'];
    $emp1 = $dto->employees[1] ?? ['type' => 'الموظف المسؤول', 'name' => 'غير محدد'];
    $isBlank = ($dto->studentId ?? 0) === 0;
@endphp
<div class="student-document-paper{{ $isBlank ? ' student-document-paper-blank' : '' }}">
    <div class="doc-header">
        <div class="doc-title">سجل قيد الطلاب الخارجيين للدراسة المهنية العامة</div>
        <div class="doc-header-right">
            <div class="doc-org">وزارة التربية</div>
            <div class="doc-org">المديرية العامة للتعليم المهني</div>
            <div class="doc-org">قسم التعليم المهني في محافظة كربلاء المقدسة</div>
        </div>
        <div class="doc-photo">صورة</div>
    </div>

    <div class="doc-row doc-row-page-meta">
        @php
            $editable = $editable ?? true;
            $pageNumber = $dto->pageNumber ?? '';
        @endphp
        <span class="doc-label doc-label-page-number">رقم الصفحة</span>
        <span class="doc-value doc-value-page-number">
            @if($editable)
                <input type="text"
                       name="page_number"
                       class="doc-field-input"
                       value="{{ $pageNumber }}"
                       placeholder=""
                       autocomplete="off"
                       data-page-input />
            @else
                {!! $cell($ad($pageNumber)) !!}
            @endif
        </span>
    </div>
    <div class="doc-row doc-row-exam-number">
        <span class="doc-label">الرقم الامتحاني</span>
        <span class="doc-value">{!! $cell($ad($dto->examNumber)) !!}</span>
    </div>
    <div class="doc-row">
        <span class="doc-label">الاسم الرباعي</span>
        <span class="doc-value doc-value-full-name">{!! $cell($dto->fullName) !!}</span>
    </div>
    <div class="doc-row doc-row-two">
        <span class="doc-label doc-w-17">التولد</span>
        <span class="doc-value doc-w-18 arabic-date" data-date="{{ $dateYmd($dto->birthDate) }}" dir="ltr">{!! $cell($dateDisplay($dto->birthDate)) !!}</span>
        <span class="doc-label">محل الولادة</span>
        <span class="doc-value doc-value-birth-place">{!! $cell($dto->birthPlace) !!}</span>
    </div>
    <div class="doc-row">
        <span class="doc-label">اسم الام الكامل</span>
        <span class="doc-value doc-value-mother-name">{!! $cell($ad($dto->motherName)) !!}</span>
    </div>
    <div class="doc-row doc-row-two">
        <span class="doc-label doc-label-branch">الفرع</span>
        <span class="doc-value doc-value-branch">{!! $cell($ad($dto->branch)) !!}</span>
        <span class="doc-label doc-label-specialization">الاختصاص</span>
        <span class="doc-value doc-value-specialization">{!! $cell($ad($dto->specialization)) !!}</span>
    </div>
    <div class="doc-row doc-row-wide">
        <span class="doc-label">آخر مدرسة كان فيها الطالب</span>
        <span class="doc-value doc-value-last-school">{!! $cell($ad($dto->lastSchool)) !!}</span>
    </div>
    <div class="doc-row doc-row-three">
        <span class="doc-label doc-w-11">رقم وثيقة المتوسطة</span>
        <span class="doc-value doc-w-13">{!! $cell($ad($dto->middleDocNumber)) !!}</span>
        <span class="doc-label doc-w-14">تاريخها</span>
        <span class="doc-value doc-value-middle-doc-date arabic-date" data-date="{{ $dateYmd($dto->middleDocDate) }}" dir="ltr">{!! $cell($dateDisplay($dto->middleDocDate)) !!}</span>
    </div>
    <div class="doc-row">
        <span class="doc-label">جهة الإصدار</span>
        <span class="doc-value doc-w-15 doc-value-issuing-authority">{!! $cell($ad($dto->issuingAuthority)) !!}</span>
    </div>
    <div class="doc-row doc-row-three">
        <span class="doc-label doc-w-11">العام الدراسي</span>
        <span class="doc-value doc-value-academic-year">{!! $cell($ad($dto->academicYear)) !!}</span>
        <span class="doc-label">الدور</span>
        <span class="doc-value doc-value-round">{!! $cell($ad($dto->round)) !!}</span>
        <span class="doc-label">النتيجة</span>
        <span class="doc-value doc-value-result">{!! $cell($ad($dto->result)) !!}</span>
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
                    <td class="doc-grade-subject">{!! $cell($row['subject'] ?? '') !!}</td>
                    <td class="doc-grade-num">{!! $cell($ad($row['score'] ?? '')) !!}</td>
                    <td class="doc-grade-words">{!! $cell($row['score_words'] ?? '') !!}</td>
                </tr>
            @endforeach
            <tr class="doc-total-row">
                <td>المجموع</td>
                <td class="doc-grade-num">{!! $cell($ad($dto->total)) !!}</td>
                <td class="doc-grade-words">{!! $cell($dto->totalWords) !!}</td>
            </tr>
        </tbody>
    </table>

    <div class="doc-completed-row">
        <span class="doc-text-title">الدروس التي أكمل بها:</span>
        <span class="doc-completed-value">@if(count($dto->subjectsCompleted) > 0){{ $ad(implode('، ', $dto->subjectsCompleted)) }}@else&nbsp;@endif</span>
    </div>

    <div class="doc-section-title"><span class="doc-text-title">الوثائق التي زود بها:</span></div>
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
            @for ($docRow = 0; $docRow < 4; $docRow++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="doc-signatures">
        <div class="doc-sig-cell">
            <div class="doc-sig-title">الموظف المسؤول</div>
        </div>
        <div class="doc-sig-cell">
            <div class="doc-sig-title">مدير القسم المهني</div>
        </div>
    </div>
</div>
