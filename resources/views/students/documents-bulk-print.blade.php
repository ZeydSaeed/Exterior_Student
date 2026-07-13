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
            top: 3.25rem;
            left: 12rem;
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
            margin: 0 0 0 0.75rem;
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }
        .page-documents-bulk .documents-bulk-top-bar .btn-primary:disabled {
            opacity: 0.65;
            cursor: wait;
        }

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

        .doc-bulk-placeholder {
            width: 100%;
            display: flex;
            justify-content: center;
            min-height: 1050px;
            margin-bottom: 1.5rem;
        }
        .doc-bulk-placeholder-inner {
            width: 42rem;
            max-width: 100%;
            min-height: 1050px;
            border: 1px dashed #c5c0b8;
            border-radius: 0.35rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8a8580;
            font-size: 0.95rem;
            background: #faf9f7;
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
            .doc-bulk-placeholder {
                display: none !important;
            }
        }
    </style>
@endsection

@section('content')
    @php $students_filter_form_action = route('students.documents.bulk-print'); @endphp

    <div class="documents-bulk-top-bar no-print" aria-label="إجراءات القيود">
        <button type="button" class="btn-primary" id="documents-bulk-btn-print">طباعة</button>
        <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary" title="إغلاق الصفحة والرجوع">إغلاق</a>
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
