@extends('layouts.dashboard')

@section('title', 'إدخال النتائج من Excel')
@section('body_class', 'page-import-excel')

@section('content')
    <div class="import-excel-wrap">
        <div class="import-excel-header">
            <h1>إدخال النتائج من Excel</h1>
        </div>

        @if(session('error'))
            <div class="import-excel-error" role="alert">{{ session('error') }}</div>
        @endif

        <p class="import-excel-desc">
            اختر ملف Excel (xlsx أو xls) يحتوي على أعمدة:<br>
            الرقم الامتحاني، اسم الطالب (الاسم الرباعي)، الفرع، الاختصاص، العام الدراسي، ثم أعمدة المواد الدراسية الخاصة بالاختصاص (من العمود 6 إلى 13)، ثم المجموع، المعدل، النتيجة.
            يجب أن تتطابق بيانات الفرع والاختصاص مع مواد الاختصاص المسجلة في النظام.
        </p>

        <form action="{{ route('students.results-import-excel.upload') }}" method="POST" enctype="multipart/form-data" class="import-excel-form">
            @csrf
            <div class="form-group import-excel-file-group">
                <div class="import-excel-file-wrap">
                    <input  style="margin-bottom: 1rem;" type="file" id="results-import-file" name="file" accept=".xlsx,.xls" required class="import-excel-file-input"/>
                </div>
            </div>
            <div class="import-excel-actions">
                <button type="submit" class="btn-primary">رفع ومعاينة</button>
                <a href="{{ route('students.index') }}" class="btn-primary btn-secondary import-excel-btn-cancel" style="text-decoration: none; font-weight: bold;">إلغاء</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var input = document.getElementById('results-import-file');
            var nameEl = document.getElementById('results-import-file-name');
            if (!input || !nameEl) return;
            input.addEventListener('change', function () {
                if (input.files && input.files.length > 0) {
                    nameEl.textContent = input.files[0].name || 'تم اختيار ملف';
                } else {
                    nameEl.textContent = 'لم يتم اختيار ملف';
                }
            });
        })();
    </script>
@endsection
