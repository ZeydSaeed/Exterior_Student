@extends('layouts.dashboard')

@section('title', 'السجل الشخصي للطالب')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-student-profile')

@section('content')
    <div class="employees-page-wrap">
        <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary employees-close-btn" title="إغلاق">إغلاق</a>

    <div class="employees-page-header">
        <h1>السجل الشخصي للطالب</h1>
    </div>

    <div class="profile-student-info">
        <p class="documents-student-line" style="text-align: start;"><strong>الرقم الامتحاني:</strong> {{ $dto->examNumber ?? '—' }}</p>
        <p class="documents-student-line" style="text-align: start;"><strong>اسم الطالب:</strong> {{ $dto->studentName ?? '—' }}</p>
    </div>

    <div class="students-layout">
        <section class="students-table-area" aria-label="السجل الشخصي">
            {{-- التأييدات --}}
            <div class="employees-card employees-card-right">
                <h2 class="employees-card-title">التأييدات</h2>
                <div class="students-table-wrapper">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>التاريخ</th>
                                <th >العدد</th>
                                <th>الى</th>
                                <th>منظم التاييد</th>
                                <th>اسم الموظف</th>
                                <th>المسؤول</th>
                                <th>اسم الموظف المسؤول</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dto->attestations as $att)
                                <tr>
                                    <td>{{ $att->type === 'with_grades' ? 'تأييد بالدرجات' : 'تأييد بدون درجات' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('students.profile.attestations.update', [$dto->studentId, $att->id]) }}" class="inline-form">
                                            @csrf
                                            @method('PUT')
                                            <input type="date" name="date" value="{{ \App\Support\ImportDateNormalizer::toYmd($att->date) ?? '' }}" class="profile-field-date arabic-date-field" lang="ar-IQ" autocomplete="off" />
                                    </td>
                                    <td>
                                            <input type="text" name="number" value="{{ $att->number ?? '' }}" class="profile-field-small" />
                                    </td>
                                    <td>
                                            <textarea name="issued_to" rows="2">{{ $att->issuedTo ?? '' }}</textarea>
                                    </td>
                                    <td>
                                            <input type="text" name="right_title" value="{{ $att->rightTitle ?? '' }}" />
                                    </td>
                                    <td>
                                            <input type="text" name="right_employee_name" value="{{ $att->rightEmployeeName ?? '' }}" />
                                    </td>
                                    <td>
                                            <input type="text" name="left_title" value="{{ $att->leftTitle ?? '' }}" />
                                    </td>
                                    <td>
                                            <input type="text" name="left_employee_name" value="{{ $att->leftEmployeeName ?? '' }}" />
                                    </td>
                                    <td class="students-table-actions">
                                        <div class="students-table-actions-inner">
                                            <button type="submit" class="btn-primary btn-edit-row">حفظ</button>
                                        </form>
                                        <form method="POST" action="{{ route('students.profile.attestations.destroy', [$dto->studentId, $att->id]) }}" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-primary btn-delete-row">حذف</button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">لا توجد تأييدات مسجلة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- الوثائق --}}
            <div class="employees-card employees-card-left">
                <h2 class="employees-card-title">الوثائق</h2>
                <div class="students-table-wrapper">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>رقم الوثيقة</th>
                                <th>تاريخها</th>
                                <th>الجهة المعنونة إليها</th>
                                <th>الغرض من الوثيقة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dto->records as $record)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('students.documents.update', [$dto->studentId, $record->id]) }}" class="inline-form">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="document_number" class="arabic-digits-input" dir="rtl" lang="ar" value="{{ \App\Support\ArabicDigits::toArabic($record->documentNumber ?? '') }}" />
                                    </td>
                                    <td>
                                            <input type="date" name="document_date" class="arabic-date-field" lang="ar-IQ" value="{{ \App\Support\ImportDateNormalizer::toYmd($record->documentDate) ?? '' }}" autocomplete="off" />
                                    </td>
                                    <td>
                                            <input type="text" name="addressee" value="{{ $record->addressee ?? '' }}" />
                                    </td>
                                    <td>
                                            <input type="text" name="purpose" value="{{ $record->purpose ?? '' }}" />
                                    </td>
                                    <td class="students-table-actions">
                                        <div class="students-table-actions-inner">
                                            <button type="submit" class="btn-primary btn-edit-row">حفظ</button>
                                        </form>
                                        <form method="POST" action="{{ route('students.documents.destroy', [$dto->studentId, $record->id]) }}" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-primary btn-delete-row">حذف</button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">لا توجد وثائق مسجلة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    </div>
@endsection

@section('styles')
<style>
    .page-student-profile .arabic-date-field-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
        width: 100%;
        height: 2rem;
        direction: rtl;
        box-sizing: border-box;
        border: 1px solid var(--color-dark-accent, #4a545e);
        border-radius: 0.25rem;
        background: #fff;
        overflow: hidden;
    }
    .page-student-profile .arabic-date-dmy {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0;
        flex: 1 1 auto;
        padding: 0 0.3rem;
        direction: rtl;
        min-width: 0;
    }
    .page-student-profile .arabic-date-part {
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
        box-shadow: none !important;
        direction: rtl;
    }
    .page-student-profile .arabic-date-day,
    .page-student-profile .arabic-date-month {
        width: 1.6rem;
    }
    .page-student-profile .arabic-date-year {
        width: 2.6rem;
    }
    .page-student-profile .arabic-date-sep {
        color: #4a545e;
        user-select: none;
    }
    .page-student-profile .arabic-date-field-wrap input[type="date"].arabic-date-field {
        position: absolute !important;
        width: 0 !important;
        min-width: 0 !important;
        max-width: 0 !important;
        height: 0 !important;
        flex: 0 0 0 !important;
        opacity: 0;
        pointer-events: none;
        overflow: hidden;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
    }
    .page-student-profile .arabic-date-field-wrap input[type="date"].arabic-date-field::-webkit-calendar-picker-indicator {
        display: none !important;
    }
    .page-student-profile .arabic-date-field-wrap input[type="date"].arabic-date-field::-webkit-datetime-edit {
        display: none;
    }

    /* جدول التأييدات: تاريخ عربي يوم/شهر/سنة من اليمين لليسار */
    .page-student-profile .employees-card-right td:nth-child(2) {
        width: 1%;
        white-space: nowrap;
    }
    .page-student-profile .employees-card-right .arabic-date-field-wrap {
        width: max-content;
        max-width: 100%;
        height: auto;
        min-height: 1.5rem;
        min-width: 6.75rem;
        padding-block: 0.1rem;
        padding-inline: 0.15rem;
    }
    .page-student-profile .employees-card-right .arabic-date-dmy {
        flex: 0 0 auto;
        padding: 0 0.35rem;
        gap: 0.05rem;
    }
    .page-student-profile .employees-card-right .arabic-date-part {
        width: auto !important;
        min-width: 1.25ch !important;
        field-sizing: content;
        font-size: 0.9rem;
        line-height: 1.3;
    }
    .page-student-profile .employees-card-right .arabic-date-day,
    .page-student-profile .employees-card-right .arabic-date-month,
    .page-student-profile .employees-card-right .arabic-date-year {
        width: auto !important;
        min-width: 1.25ch !important;
    }
    .page-student-profile .employees-card-right .arabic-date-sep {
        font-size: 0.9rem;
        line-height: 1.3;
        margin: 0 0.05rem;
        padding: 0;
    }

    /* جدول الوثائق: ملاء تلقائي لحجم التاريخ */
    .page-student-profile .employees-card-left td:nth-child(2) {
        width: 1%;
        white-space: nowrap;
    }
    .page-student-profile .employees-card-left .arabic-date-field-wrap {
        width: max-content;
        max-width: 100%;
        height: auto;
        min-height: 1.5rem;
        min-width: 6.75rem;
        padding-block: 0.1rem;
        padding-inline: 0.15rem;
    }
    .page-student-profile .employees-card-left .arabic-date-dmy {
        flex: 0 0 auto;
        padding: 0 0.35rem;
        gap: 0.05rem;
    }
    .page-student-profile .employees-card-left .arabic-date-part {
        width: auto !important;
        min-width: 1.25ch !important;
        field-sizing: content;
        font-size: 0.9rem;
        line-height: 1.3;
    }
    .page-student-profile .employees-card-left .arabic-date-day,
    .page-student-profile .employees-card-left .arabic-date-month,
    .page-student-profile .employees-card-left .arabic-date-year {
        width: auto !important;
        min-width: 1.25ch !important;
    }
    .page-student-profile .employees-card-left .arabic-date-sep {
        font-size: 0.9rem;
        line-height: 1.3;
        margin: 0 0.05rem;
        padding: 0;
    }
</style>
@endsection

@section('scripts')
<script src="{{ url('js/arabic-date.js') }}?v={{ file_exists(public_path('js/arabic-date.js')) ? filemtime(public_path('js/arabic-date.js')) : time() }}"></script>
<script>
(function () {
    'use strict';
    function resizeTextarea(ta) {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
    }
    function initProfileTableTextareas() {
        var textareas = document.querySelectorAll('.page-student-profile .students-table textarea');
        textareas.forEach(function (ta) {
            resizeTextarea(ta);
            ta.addEventListener('input', function () { resizeTextarea(ta); });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProfileTableTextareas);
    } else {
        initProfileTableTextareas();
    }
})();
</script>
@endsection
