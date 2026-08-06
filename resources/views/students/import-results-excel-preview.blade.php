@extends('layouts.dashboard')

@section('title', 'معاينة إدخال النتائج')
@section('body_class', 'page-import-excel-preview')

@section('content')
    <div class="import-excel-wrap">
        <div class="import-excel-header">
            <h1>معاينة إدخال النتائج</h1>
        </div>

        <p class="import-excel-stats">
            المجموع: {{ $total }} | صالح: <strong>{{ $validCount }}</strong> | فاشل: <strong>{{ $failedCount }}</strong>
            @if(($selectedRound ?? '') !== '')
                | الدور: <strong>{{ $selectedRound }}</strong>
            @endif
        </p>

        <div class="import-excel-preview-actions">
            <form action="{{ route('students.results-import-excel.process') }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $batchId }}" />
                <button  style="margin-bottom: 1rem;" type="submit" class="btn-primary" @if($validCount === 0) disabled @endif>
                    ترحيل الصفوف الصالحة ({{ $validCount }})
                </button>
            </form>
            <a href="{{ route('students.results-import-excel') }}" class="btn-primary btn-secondary" style="text-decoration: none; font-weight: bold;">رفع ملف جديد</a>
            <a href="{{ route('students.index') }}" class="btn-primary btn-secondary" style="text-decoration: none; font-weight: bold;">العودة للقائمة</a>
        </div>

        <div class="students-table-wrapper">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الرقم الامتحاني</th>
                        <th>اسم الطالب</th>
                        <th>الفرع</th>
                        <th>الاختصاص</th>
                        <th>العام الدراسي</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>الخطأ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr class="students-data-row import-excel-row status-{{ $row->status }}">
                            <td>{{ $row->row_index }}</td>
                            <td>{{ e($row->exam_number ?? '') }}</td>
                            <td>{{ e($row->student_name ?? '') }}</td>
                            <td>{{ e($row->branch ?? '') }}</td>
                            <td>{{ e($row->major ?? '') }}</td>
                            <td>{{ e($row->academic_year ?? '') }}</td>
                            <td>{{ e($row->round ?? '') }}</td>
                            <td>
                                @if($row->status === 'valid')
                                    <span class="status-badge status-valid">صالح</span>
                                @else
                                    <span class="status-badge status-failed">فاشل</span>
                                @endif
                            </td>
                            <td class="error-cell">{{ e($row->error ?? '') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
