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
                                            <input type="date" name="date" value="{{ $att->date ?? '' }}" class="profile-field-date" />
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
                                            <input type="text" name="document_number" value="{{ $record->documentNumber ?? '' }}" />
                                    </td>
                                    <td>
                                            <input type="date" name="document_date" value="{{ $record->documentDate ?? '' }}" />
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

@section('scripts')
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
