@extends('layouts.dashboard')

@section('title', 'سجل قيد الطالب')
@section('body_class', 'page-student-document')

@section('styles')
    <link rel="stylesheet" href="{{ url('css/student-document.css') }}?v={{ file_exists(public_path('css/student-document.css')) ? filemtime(public_path('css/student-document.css')) : time() }}">
    <style>
        /* شريط علوي مطابق لأسلوب صفحة القيود */
        .page-student-document .student-document-actions {
            position: fixed;
            top: 4.5rem;
            left: 12rem;
            right: 3rem;
            z-index: 45;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            gap: 0.5rem;
            margin: 0;
            width: auto;
            max-width: none;
            direction: rtl;
        }
        .page-student-document .student-document-actions .btn-primary {
            margin: 0;
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }
        .page-student-document .student-document-actions #student-document-btn-print {
            margin-right: 0.75rem;
        }
        .page-student-document .dashboard-content {
            padding-top: 2.35rem;
        }
        .page-student-document .student-document-layout {
            max-width: none;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 0;
        }
        .page-student-document .doc-page-wrap {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .page-student-document .student-document-paper {
            margin: 0 !important;
            background: #fff;
            width: 42rem !important;
            max-width: 42rem !important;
            min-width: 42rem !important;
            box-sizing: border-box;
        }
        @media print {
            .page-student-document .student-document-actions,
            .page-student-document .doc-bulk-view-footer {
                display: none !important;
            }
            .page-student-document .dashboard-content {
                padding-top: 0 !important;
            }
            .page-student-document .student-document-paper {
                zoom: 1 !important;
                transform: none !important;
                width: auto !important;
                max-width: none !important;
                min-width: 0 !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="student-document-layout"
         data-student-id="{{ $dto->studentId }}"
         data-update-url="{{ route('students.document.update', ['id' => $dto->studentId]) }}">
        <div class="student-document-actions no-print">
            <button type="button" class="btn-primary" id="student-document-btn-print">طباعة</button>
            <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary btn-close">إغلاق</a>
        </div>

        <div class="doc-page-wrap">
            @include('students.partials.document-paper', ['dto' => $dto, 'editable' => false])
            <div class="doc-bulk-view-footer no-print" aria-label="رقم الصفحة">
                {{ \App\Support\ArabicDigits::toArabic('1') }} / {{ \App\Support\ArabicDigits::toArabic('1') }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ url('js/arabic-date.js') }}?v={{ file_exists(public_path('js/arabic-date.js')) ? filemtime(public_path('js/arabic-date.js')) : time() }}"></script>
    <script src="{{ url('js/students/document.js') }}?v={{ file_exists(public_path('js/students/document.js')) ? filemtime(public_path('js/students/document.js')) : time() }}"></script>
@endsection
