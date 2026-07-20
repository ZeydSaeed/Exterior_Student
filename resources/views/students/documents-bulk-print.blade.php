@extends('layouts.dashboard')

@section('title', 'القيود')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-students page-documents-bulk')

@section('toolbar_center')
    @include('students.partials.toolbar-filter-summary', ['useStudentListSessionMerge' => false])
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ url('css/student-document.css') }}?v={{ file_exists(public_path('css/student-document.css')) ? filemtime(public_path('css/student-document.css')) : time() }}">
    <style>
        .page-documents-bulk .documents-bulk-top-bar {
            position: fixed;
            top: 4.5rem;
            left: 12rem;
            right: 3rem;
            z-index: 45;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.02rem;
            margin: 0;
            direction: ltr;
        }
        .page-documents-bulk .documents-bulk-top-bar-actions {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            direction: rtl;
        }
        .page-documents-bulk .documents-bulk-top-bar .btn-primary {
            margin: 0;
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }
        .page-documents-bulk .documents-bulk-top-bar .btn-primary:disabled {
            opacity: 0.65;
            cursor: wait;
        }
        .page-documents-bulk .documents-bulk-count {
            margin: 0;
            margin-left: auto;
            margin-right: 0.85rem;
            padding: 0.40rem 0.75rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: #200f1b;
            background: #f1ebe3;
            border: 1px solid #d6d2cd;
            border-radius: 0.35rem;
            white-space: nowrap;
            direction: rtl;
        }

        /* خلفية موحّدة لصفحة القيود = نفس لون خلفية الداشبورد */
        body.page-documents-bulk,
        body.page-documents-bulk .dashboard-wrap,
        body.page-documents-bulk .dashboard-main,
        body.page-documents-bulk .dashboard-content,
        body.page-documents-bulk .employees-page-wrap,
        body.page-documents-bulk .students-layout,
        body.page-documents-bulk .students-table-area,
        body.page-documents-bulk .student-document-layout,
        body.page-documents-bulk .doc-bulk-page-break,
        body.page-documents-bulk .doc-bulk-placeholder {
            background: var(--color-light-accent) !important;
        }

        .page-documents-bulk .employees-page-wrap {
            padding-top: 2.35rem;
        }
        .page-documents-bulk .students-table-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.15rem;
            border-radius: 0;
            min-height: calc(100vh - 6.5rem);
            box-shadow: none;
            overflow-x: hidden;
        }
        .page-documents-bulk .student-document-layout {
            max-width: none;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .page-documents-bulk .student-document-paper {
            margin: 0;
            background: #fff;
            width: 42rem !important;
            max-width: 42rem !important;
            min-width: 42rem !important;
            box-sizing: border-box;
        }
        .page-documents-bulk .doc-bulk-placeholder-inner {
            width: 42rem !important;
            max-width: 42rem !important;
            min-width: 42rem !important;
            box-sizing: border-box;
        }

        .page-documents-bulk .doc-bulk-page-break {
            page-break-after: always;
            break-after: page;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }
        .page-documents-bulk .doc-bulk-page-break:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .doc-bulk-placeholder {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 0;
            margin-bottom: 0.75rem;
        }
        .doc-bulk-placeholder-inner {
            width: 42rem !important;
            max-width: 42rem !important;
            min-width: 42rem !important;
            min-height: 1050px;
            border: 1px dashed #b8b3ad;
            border-radius: 0.35rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a545e;
            font-size: 0.95rem;
            background: var(--color-light-accent);
            box-sizing: border-box;
        }
        .doc-bulk-placeholder.is-loading .doc-bulk-placeholder-inner {
            color: #4a545e;
        }

        .documents-bulk-progress-overlay {
            position: fixed;
            inset: 0;
            z-index: 200;
            background: rgba(15, 23, 42, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .documents-bulk-progress-overlay[hidden] {
            display: none !important;
        }
        .documents-bulk-progress-panel {
            background: #fff;
            border-radius: 0.5rem;
            padding: 1.5rem 1.75rem;
            min-width: min(22rem, 100%);
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.2);
            text-align: center;
        }
        .documents-bulk-progress-panel h2 {
            margin: 0 0 0.75rem;
            font-size: 1.1rem;
            color: #200f1b;
        }
        .documents-bulk-progress-bar {
            height: 0.65rem;
            background: #e8e4df;
            border-radius: 999px;
            overflow: hidden;
            margin: 0.75rem 0;
        }
        .documents-bulk-progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #bfb487, #200f1b);
            border-radius: 999px;
            transition: width 0.2s ease;
        }
        .documents-bulk-progress-text {
            margin: 0;
            font-size: 0.95rem;
            color: #4a545e;
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
            .page-documents-bulk .students-table-area {
                min-height: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }
            .page-documents-bulk .doc-bulk-page-break {
                height: auto !important;
                width: auto !important;
                overflow: visible !important;
                margin: 0 !important;
            }
            .page-documents-bulk .student-document-paper,
            .page-documents-bulk .doc-bulk-placeholder-inner {
                zoom: 1 !important;
                transform: none !important;
                width: auto !important;
                max-width: none !important;
                min-width: 0 !important;
            }
            .doc-bulk-placeholder {
                display: none !important;
            }
        }
    </style>
@endsection

@section('content')
    @php $students_filter_form_action = route('students.documents.bulk-print'); @endphp

    <div class="documents-bulk-top-bar no-print" aria-label="إجراءات القيود">
        @php
            $documentsBulkCount = ! empty($showBlankDocument) ? 0 : count($studentIds ?? []);
        @endphp
        <div class="documents-bulk-top-bar-actions">
            <button type="button" class="btn-primary" id="documents-bulk-btn-print">طباعة</button>
            <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary" title="إغلاق الصفحة والرجوع">إغلاق</a>
        </div>
        <span class="documents-bulk-count" aria-label="عدد القيود المتوفرة">
            عدد القيود: {{ \App\Support\ArabicDigits::toArabic((string) $documentsBulkCount) }}
        </span>
    </div>

    <div id="documents-bulk-progress" class="documents-bulk-progress-overlay no-print" hidden aria-hidden="true" role="dialog" aria-labelledby="documents-bulk-progress-title">
        <div class="documents-bulk-progress-panel">
            <h2 id="documents-bulk-progress-title">جاري تجهيز القيود للطباعة</h2>
            <div class="documents-bulk-progress-bar" aria-hidden="true">
                <div class="documents-bulk-progress-fill" id="documents-bulk-progress-fill"></div>
            </div>
            <p class="documents-bulk-progress-text" id="documents-bulk-progress-text">0 / 0</p>
        </div>
    </div>

    <div class="employees-page-wrap">
        @include('students.partials.filters', ['useStudentListSessionMerge' => false])

        <div class="students-layout">
            <section class="students-table-area" aria-label="معاينة القيود وطباعتها">
                @if(!empty($showBlankDocument))
                    <div id="documents-bulk-root" class="student-document-layout" data-chunk-url="" data-chunk-size="5" data-total="0">
                        <div class="doc-bulk-page-break doc-bulk-loaded" data-index="0" data-student-id="0">
                            @include('students.partials.document-paper', ['dto' => $blankDto, 'editable' => false])
                        </div>
                    </div>
                @else
                    <div
                        id="documents-bulk-root"
                        class="student-document-layout"
                        data-chunk-url="{{ route('students.documents.bulk-print.chunk', request()->query()) }}"
                        data-chunk-size="5"
                        data-total="{{ count($studentIds) }}"
                    >
                        @forelse($studentIds as $index => $studentId)
                            @if(isset($initialDtosById[$studentId]))
                                <div class="doc-bulk-page-break doc-bulk-loaded" data-index="{{ $index }}" data-student-id="{{ $studentId }}">
                                    @include('students.partials.document-paper', ['dto' => $initialDtosById[$studentId], 'editable' => false])
                                </div>
                            @else
                                <div class="doc-bulk-page-break doc-bulk-placeholder" data-index="{{ $index }}" data-student-id="{{ $studentId }}" data-loaded="0">
                                    <div class="doc-bulk-placeholder-inner">—</div>
                                </div>
                            @endif
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
    <script src="{{ url('js/arabic-date.js') }}?v={{ file_exists(public_path('js/arabic-date.js')) ? filemtime(public_path('js/arabic-date.js')) : time() }}"></script>
    <script src="{{ url('js/students/documents-bulk-lazy.js') }}?v={{ file_exists(public_path('js/students/documents-bulk-lazy.js')) ? filemtime(public_path('js/students/documents-bulk-lazy.js')) : time() }}"></script>
    <script src="{{ url('js/students/index.js') }}?v={{ file_exists(public_path('js/students/index.js')) ? filemtime(public_path('js/students/index.js')) : time() }}"></script>
@endsection
