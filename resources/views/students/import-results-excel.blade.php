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

        @if($errors->any())
            <div class="import-excel-error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <p class="import-excel-desc">
            اختر الدور ثم ملف Excel (xlsx أو xls) يحتوي على أعمدة:<br>
            الرقم الامتحاني، اسم الطالب (اختياري للعرض فقط)، الفرع، الاختصاص، العام الدراسي، ثم أعمدة المواد الدراسية الخاصة بالاختصاص (من العمود 6 إلى 13)، ثم المجموع، المعدل، النتيجة.
            المطابقة تتم بالرقم الامتحاني مع الفرع والاختصاص والعام الدراسي (دون الاعتماد على الاسم). درجات المواد تقبل الأرقام أو النصوص (مثل غ أو حجب). الدور المختار يُحفظ مع النتائج المستوردة.
        </p>

        <form action="{{ route('students.results-import-excel.upload') }}" method="POST" enctype="multipart/form-data" class="import-excel-form">
            @csrf
            <div class="form-group">
                <label for="results-import-round">الدور</label>
                <select id="results-import-round" name="round" required class="import-excel-select" aria-label="الدور">
                    <option value="" disabled @selected(old('round') === null || old('round') === '')>اختر الدور</option>
                    @foreach(config('grades_catalog.round_options', []) as $opt)
                        <option value="{{ $opt }}" @selected(old('round') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group import-excel-file-group">
                <div class="import-excel-file-wrap">
                    <input style="margin-bottom: 1rem;" type="file" id="results-import-file" name="file" accept=".xlsx,.xls" required class="import-excel-file-input"/>
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
