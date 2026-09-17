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

        <p class="import-excel-desc">اختر الدور ثم ملف Excel (xlsx أو xls) يحتوي على الأعمدة التالية بالترتيب:</p>

        <div class="import-excel-table-wrap" role="region" aria-label="ترتيب أعمدة ملف Excel للنتائج">
            <table class="import-excel-table import-excel-columns-table" data-fit-table="true">
                <thead>
                    <tr>
                        @foreach($excelColumns as $column)
                            <th scope="col">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($excelColumns as $column)
                            <td>&nbsp;</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="import-excel-desc">أعمدة المواد من المادة 1 إلى المادة 8 حسب ترتيب مواد الاختصاص في الملف. المطابقة تتم بالرقم الامتحاني مع الفرع والاختصاص ( يجب ان تكون المطابقة دقيقة وبدون اي اختلاف) والعام الدراسي (دون الاعتماد على الاسم). درجات المواد تقبل الأرقام أو النصوص (مثل غ أو حجب). الدور المختار يُحفظ مع النتائج المستوردة.</p>

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
    <script src="{{ url('js/import-excel-fit.js') }}?v={{ file_exists(public_path('js/import-excel-fit.js')) ? filemtime(public_path('js/import-excel-fit.js')) : time() }}"></script>
@endsection
