@extends('layouts.dashboard')

@section('title', 'استيراد طلاب من Excel')
@section('body_class', 'page-import-excel')

@section('content')
    <div class="import-excel-wrap">
        <div class="import-excel-header">
            <h1>استيراد طلاب من Excel</h1>
        </div>

        @if(session('error'))
            <div class="import-excel-error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <p class="import-excel-desc">اختر ملف Excel (xlsx أو xls) يحتوي على أعمدة:<br>الرقم الامتحاني، اسم الطالب، اسم الاب، اسم الجد، اللقب، الجنس، التولد، محل الولادة، اسم الام الكامل، الفرع، الاختصاص، العام الدراسي، اخر مدرسة، رقم الوثيقة، تاريخها، جهة الاصدار.<br>صيغة التواريخ المقبولة: يوم/شهر/سنة مثل 15/06/2006</p>

        <form action="{{ route('students.import-excel.upload') }}" method="POST" enctype="multipart/form-data" class="import-excel-form">
            @csrf
            <div class="form-group import-excel-file-group" style="margin-bottom: 0.5rem;">
                <div class="import-excel-file-wrap">
                    <input type="file" id="import-file" name="file" accept=".xlsx,.xls" required aria-label="اختيار ملف" />
                </div>
            </div>
            <div class="import-excel-actions">
                <button type="submit" class="btn-primary">رفع ومعاينة</button>
                <a href="{{ route('students.index') }}" class="btn-primary btn-secondary import-excel-btn-cancel" style="text-decoration: none; font-weight: bold;">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
