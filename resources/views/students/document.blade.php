@extends('layouts.dashboard')

@section('title', 'سجل قيد الطالب')
@section('body_class', 'page-student-document')

@section('styles')
    <link rel="stylesheet" href="{{ url('css/student-document.css') }}?v={{ file_exists(public_path('css/student-document.css')) ? filemtime(public_path('css/student-document.css')) : time() }}">
@endsection

@section('content')
    <div class="student-document-layout"
         data-student-id="{{ $dto->studentId }}"
         data-update-url="{{ route('students.document.update', ['id' => $dto->studentId]) }}">
        <div class="student-document-actions no-print">
            <button type="button" class="btn-primary" id="student-document-btn-print">طباعة</button>
            <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary btn-close">إغلاق</a>
        </div>

        @include('students.partials.document-paper', ['dto' => $dto])
    </div>
@endsection

@section('scripts')
    <script src="{{ url('js/students/document.js') }}?v={{ file_exists(public_path('js/students/document.js')) ? filemtime(public_path('js/students/document.js')) : time() }}"></script>
@endsection
