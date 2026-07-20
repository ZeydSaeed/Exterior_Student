@extends('layouts.dashboard')

@section('title', 'وثائق الطالب')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-student-documents')

@section('content')
    <div class="documents-page-wrap">
        <div class="documents-page-actions">
            @if($dto->previousStudentId)
                <a href="{{ route('students.documents.index', ['id' => $dto->previousStudentId]) }}" class="btn-primary documents-nav-btn documents-prev-btn" title="الطالب السابق حسب ترتيب جدول الطلاب">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span>السابق</span>
                </a>
            @endif
            @if($dto->nextStudentId)
                <a href="{{ route('students.documents.index', ['id' => $dto->nextStudentId]) }}" class="btn-primary documents-nav-btn documents-next-btn" title="الطالب التالي حسب ترتيب جدول الطلاب">
                    <span>التالي</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </a>
            @endif
            <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary documents-close-btn" title="إغلاق">إغلاق</a>
        </div>

    <div class="employees-page-header documents-page-header">
        <h1>وثائق الطالب</h1>
    </div>

    @if(session('success'))
        <p class="employees-success">{{ session('success') }}</p>
    @endif

    <div class="students-layout">
        <section class="students-table-area" aria-label="وثائق الطالب">
            <div class="documents-student-info">
                <div class="documents-student-info-row">
                    <p class="documents-student-line"><strong>الرقم الامتحاني:</strong> {{ $dto->examNumber ?: '—' }}</p>
                </div>
                <div class="documents-student-info-row">
                    <p class="documents-student-line"><strong>اسم الطالب:</strong> {{ $dto->studentName ?: '—' }}</p>
                </div>
                <div class="documents-student-info-row">
                    <p class="documents-student-line"><strong>الفرع:</strong> {{ $dto->branch ?: '—' }}</p>
                    <p class="documents-student-line"><strong>الاختصاص:</strong> {{ $dto->major ?: '—' }}</p>
                </div>
                <div class="documents-student-info-row">
                    <p class="documents-student-line"><strong>العام الدراسي:</strong> {{ $dto->academicYear ?: '—' }}</p>
                    <p class="documents-student-line"><strong>الدور:</strong> {{ $dto->round ?: '—' }}</p>
                    <p class="documents-student-line"><strong>الجنس:</strong> {{ $dto->gender ?: '—' }}</p>
                </div>
            </div>

            <div class="employees-card employees-card-documents">
                <div class="employees-card-add">
                    <h3 class="employees-add-title">إضافة وثيقة جديدة</h3>
                    <form method="POST" action="{{ route('students.documents.store', $dto->studentId) }}" class="students-search-form document-add-form">
                        @csrf
                        <div class="document-add-fields">
                            <input type="text" name="document_number" class="arabic-digits-input" dir="rtl" lang="ar" value="{{ \App\Support\ArabicDigits::toArabic(old('document_number')) }}" placeholder="رقم الوثيقة..." />
                            <input type="date" name="document_date" class="arabic-date-field" lang="ar-IQ" value="{{ old('document_date') ? \App\Support\ImportDateNormalizer::toYmd(old('document_date')) : '' }}" autocomplete="off" />
                            <textarea name="addressee" class="doc-field-addressee" rows="1" placeholder="الجهة المعنونة إليها...">{{ old('addressee') }}</textarea>
                            <input type="text" name="purpose" value="{{ old('purpose') }}" placeholder="الغرض من الوثيقة..." />
                            <button type="submit" class="btn-primary">إضافة وثيقة</button>
                        </div>
                    </form>
                </div>

                <h2 class="employees-card-title">جدول الوثائق</h2>
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
                                            <textarea name="addressee" class="doc-field-addressee" rows="1">{{ $record->addressee ?? '' }}</textarea>
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
                                    <td colspan="5">لا توجد وثائق مسجلة لهذا الطالب.</td>
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
        .page-student-documents .arabic-date-field-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.15rem;
            width: 100%;
            min-width: 11.5rem;
            direction: rtl;
            box-sizing: border-box;
            border: 1px solid var(--color-dark-accent);
            border-radius: 0.25rem;
            background: #fff;
            padding: 0 0.15rem 0 0;
            overflow: hidden;
        }
        .page-student-documents .document-add-fields .arabic-date-field-wrap {
            width: auto;
            min-width: 12rem;
            height: 2.15rem;
        }
        .page-student-documents .students-table .arabic-date-field-wrap {
            height: 2rem;
        }
        .page-student-documents .arabic-date-dmy {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 0.1rem;
            flex: 1 1 auto;
            min-width: 0;
            padding: 0 0.35rem;
            direction: rtl;
        }
        .page-student-documents .arabic-date-part {
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
        .page-student-documents .arabic-date-day,
        .page-student-documents .arabic-date-month {
            width: 1.6rem;
        }
        .page-student-documents .arabic-date-year {
            width: 2.6rem;
        }
        .page-student-documents .arabic-date-sep {
            color: #4a545e;
            user-select: none;
            flex: 0 0 auto;
        }
        .page-student-documents .arabic-date-field-wrap input[type="date"].arabic-date-field {
            position: relative;
            flex: 0 0 2rem;
            width: 2rem !important;
            min-width: 2rem !important;
            max-width: 2rem !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent;
            color: transparent;
            cursor: pointer;
        }
        .page-student-documents .arabic-date-field-wrap input[type="date"].arabic-date-field::-webkit-calendar-picker-indicator {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            cursor: pointer;
            opacity: 1;
        }
        .page-student-documents .arabic-date-field-wrap input[type="date"].arabic-date-field::-webkit-datetime-edit {
            display: none;
        }

        /* جدول الوثائق: إخفاء أيقونة التاريخ بالكامل */
        .page-student-documents .students-table .arabic-date-field-wrap input[type="date"].arabic-date-field {
            position: absolute !important;
            width: 0 !important;
            min-width: 0 !important;
            max-width: 0 !important;
            height: 0 !important;
            flex: 0 0 0 !important;
            opacity: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .page-student-documents .students-table .arabic-date-field-wrap input[type="date"].arabic-date-field::-webkit-calendar-picker-indicator {
            display: none !important;
        }

        /* إضافة وثيقة: أيقونة تاريخ صغيرة جداً */
        .page-student-documents .document-add-fields .arabic-date-field-wrap input[type="date"].arabic-date-field {
            flex: 0 0 0.85rem;
            width: 1.85rem !important;
            min-width: 1.85rem !important;
            max-width: 1.85rem !important;
        }
        .page-student-documents .document-add-fields .arabic-date-field-wrap input[type="date"].arabic-date-field::-webkit-calendar-picker-indicator {
            transform: scale(0.45);
            transform-origin: center;
            opacity: 0.85;
        }
        .page-student-documents .document-add-fields input[type="text"]:not(.arabic-date-part) {
            height: 2.15rem;
            box-sizing: border-box;
        }
        .page-student-documents .students-table input[type="text"]:not(.arabic-date-part) {
            height: 2rem;
            box-sizing: border-box;
        }
        .page-student-documents .students-table th:nth-child(3),
        .page-student-documents .students-table td:nth-child(3) {
            min-width: 11rem;
            width: 20%;
        }
        .page-student-documents .document-add-fields textarea.doc-field-addressee,
        .page-student-documents .students-table textarea.doc-field-addressee,
        .page-student-documents .students-table textarea[name="addressee"] {
            width: 100%;
            min-width: 25rem;
            min-height: 2rem;
            height: 2rem;
            max-height: 4.5rem;
            resize: vertical;
            box-sizing: border-box;
            padding: 0.2rem 0.4rem;
            border: 1px solid var(--color-dark-accent);
            border-radius: 0.25rem;
            font: inherit;
            line-height: 1.25;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            word-break: break-word;
            overflow-y: auto;
        }
        .page-student-documents .document-add-fields textarea.doc-field-addressee {
            min-height: 2.15rem;
            height: 2.15rem;
            flex: 0 1 10rem;
            max-width: 14rem;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ url('js/arabic-date.js') }}?v={{ file_exists(public_path('js/arabic-date.js')) ? filemtime(public_path('js/arabic-date.js')) : time() }}"></script>
@endsection
