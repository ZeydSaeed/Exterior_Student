@extends('layouts.dashboard')

@section('title', 'بيانات الطلبة')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-students')

@section('toolbar_center')
    @include('students.partials.toolbar-filter-summary', ['useStudentListSessionMerge' => true])
@endsection

@section('content')
    @if(!empty($flash_error) || !empty($flash_status))
        <div class="flash-overlay" role="alert">
            <div class="flash-box {{ !empty($flash_error) ? 'flash-box-error' : 'flash-box-success' }}">
                <span class="flash-text">{{ $flash_error ?? $flash_status }}</span>
                <button type="button" class="flash-close" onclick="this.closest('.flash-overlay').style.display='none'">&times;</button>
            </div>
        </div>
        <script>
            // إخفاء رسالة الحالة تلقائياً بعد 20 ثانية
            document.addEventListener('DOMContentLoaded', function () {
                var overlay = document.querySelector('.flash-overlay');
                if (overlay) {
                    setTimeout(function () {
                        overlay.style.display = 'none';
                    }, 2000);
                }
            });
        </script>
    @endif

    <script>
        window.STUDENTS_GRADES_URL_TEMPLATE = "{{ route('students.grades', ['id' => '__ID__']) }}";
        window.STUDENTS_GRADES_UPDATE_URL_TEMPLATE = "{{ route('students.grades.update', ['id' => '__ID__']) }}";
        window.STUDENTS_CERTIFICATE_URL_TEMPLATE = "{{ route('students.certificate', ['id' => '__ID__']) }}";
        window.STUDENTS_CSRF_TOKEN = "{{ csrf_token() }}";
        window.STUDENTS_BULK_PRINT_URL = "{{ route('students.documents.bulk-print') }}";
        window.STUDENTS_DELETE_FAILED_URL = "{{ route('students.failures.destroy') }}";
    </script>

    @include('students.partials.filters')

    <div class="students-layout">
        <section class="students-table-area" aria-label="جدول الطلبة">
            @include('students.partials.table')
            @include('students.partials.pagination')
        </section>
    </div>

    @include('students.partials.grades-modal')
    @include('students.partials.certificate-modal')
    @include('students.partials.failures-modal')
@endsection

@section('scripts')
    <script src="{{ url('js/students/index.js') }}?v={{ file_exists(public_path('js/students/index.js')) ? filemtime(public_path('js/students/index.js')) : time() }}"></script>
@endsection
