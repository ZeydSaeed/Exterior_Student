@extends('layouts.dashboard')

@section('title', 'القيود')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-students page-documents-bulk')

@section('toolbar_center')
    @include('students.partials.toolbar-filter-summary', ['useStudentListSessionMerge' => true])
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ url('css/student-document.css') }}?v={{ file_exists(public_path('css/student-document.css')) ? filemtime(public_path('css/student-document.css')) : time() }}">
    <style>
        /* أزرار الإغلاق والطباعة — الركن العلوي الأيسر (ثابتة مع التمرير) */
        /* الركن العلوي الأيسر من نافذة المتصفح (يُحجز مساحة تحت التولبار عند الحاجة) */
        .page-documents-bulk .documents-bulk-top-bar {
            position: fixed;
            top: 3.25rem;
            left: 12rem; /* إبعاد الشريط عن حافة الصفحة اليسرى */
            right: auto;
            z-index: 45;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.02rem;
            margin: 0;
        }
        .page-documents-bulk .documents-bulk-top-bar .btn-primary {
            margin: 0 0 0 0.75rem; /* مسافة يسار بين زر الطباعة وزر الإغلاق */
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }

        /* القيود في وسط الصفحة */
        .page-documents-bulk .employees-page-wrap {
            padding-top: 3rem;
        }
        .page-documents-bulk .students-table-area {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .page-documents-bulk .student-document-layout {
            max-width: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .page-documents-bulk .student-document-paper {
            margin-inline: auto;
        }
        .page-documents-bulk .employees-page-header {
            text-align: center;
            width: 100%;
        }
        .page-documents-bulk .employees-page-header h1 {
            margin-inline: auto;
        }

        .page-documents-bulk .doc-bulk-page-break {
            page-break-after: always;
            break-after: page;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .page-documents-bulk .doc-bulk-page-break:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .documents-bulk-filters-required {
            max-width: 28rem;
            margin: 2rem auto;
            padding: 1.5rem;
            background: #f8f4f0;
            border: 1px solid #d6d2cd;
            border-radius: 0.5rem;
            text-align: center;
        }
        .documents-bulk-filters-required-text {
            margin: 0;
            font-size: 1rem;
            line-height: 1.6;
            color: #4a545e;
        }

        @media print {
            /* إخفاء واجهة الداشبورد؛ تنسيق القيد والهوامش والحقول من student-document.css (مثل صفحة document) */
            .page-documents-bulk .documents-bulk-top-bar,
            .page-documents-bulk .employees-close-btn,
            .page-documents-bulk .students-filter-sidebar,
            .page-documents-bulk .dashboard-toolbar,
            .page-documents-bulk .dashboard-sidebar,
            .no-print {
                display: none !important;
            }
            .page-documents-bulk .employees-page-wrap {
                padding-top: 0 !important;
            }
        }
    </style>
@endsection

@section('content')
    @php $students_filter_form_action = route('students.documents.bulk-print'); @endphp

    {{-- شريط ثابت أعلى يسار: إغلاق الصفحة + طباعة + إغلاق (رجوع) --}}
    <div class="documents-bulk-top-bar no-print" aria-label="إجراءات القيود">
        <button type="button" class="btn-primary" id="documents-bulk-btn-print">طباعة</button>
        <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary" title="إغلاق الصفحة والرجوع">إغلاق</a>
    </div>

    <div class="employees-page-wrap">
        

        @include('students.partials.filters')

        <div class="students-layout">
            <section class="students-table-area" aria-label="معاينة القيود وطباعتها">
                @if(!empty($filtersRequired))
                    <div class="documents-bulk-filters-required" role="alert">
                        <p class="documents-bulk-filters-required-text">يرجى تحديد <strong>الفرع</strong> و<strong>الاختصاص</strong> و<strong>العام الدراسي</strong> من الفلاتر في القائمة الجانبية، ثم النقر على «تطبيق» أو اختيار القيم لعرض القيود.</p>
                    </div>
                @else
                    <div class="student-document-layout">
                        @forelse($dtos as $dto)
                            <div class="doc-bulk-page-break">
                                @include('students.partials.document-paper', ['dto' => $dto, 'editable' => false])
                            </div>
                        @empty
                            <p class="employees-success" style="text-align:center;">لا يوجد طلاب مطابقون للفلاتر المحددة.</p>
                        @endforelse
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var btn = document.getElementById('documents-bulk-btn-print');
            if (btn && typeof window.print === 'function') {
                btn.addEventListener('click', function () { window.print(); });
            }
        })();
    </script>
    <script src="{{ url('js/students/index.js') }}?v={{ file_exists(public_path('js/students/index.js')) ? filemtime(public_path('js/students/index.js')) : time() }}"></script>
@endsection
