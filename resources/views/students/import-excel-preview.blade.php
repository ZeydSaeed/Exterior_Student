@extends('layouts.dashboard')

@section('title', 'معاينة استيراد الطلاب')
@section('body_class', 'page-import-excel-preview')

@section('content')
    <div class="import-excel-wrap">
        <div class="import-excel-header">
            <h1>معاينة استيراد الطلاب</h1>
        </div>

        <p class="import-excel-stats">
            المجموع: {{ $total }} | صالح: <strong>{{ $validCount }}</strong> | فاشل: <strong>{{ $failedCount }}</strong>
        </p>

        <div class="import-excel-preview-actions">
            <form action="{{ route('students.import-excel.process') }}" method="POST" style="display:inline; ">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $batchId }}" />
                <button style="margin-bottom: 0.5rem;" type="submit" class="btn-primary" @if($validCount === 0) disabled @endif>
                    استيراد الصفوف الصالحة ({{ $validCount }}) 
                </button>
            </form>
            <a href="{{ route('students.import-excel') }}" class="btn-primary btn-secondary" style="text-decoration: none; font-weight: bold;">رفع ملف جديد</a>
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
                        <th>الحالة</th>
                        <th>الخطأ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr class="students-data-row import-excel-row status-{{ $row->status }}">
                            <td>{{ $row->row_index }}</td>
                            <td>{{ e($row->exam_number ?? '') }}</td>
                            <td>
                                {{ e(trim(($row->first_name ?? '') . ' ' . ($row->father ?? '') . ' ' . ($row->grandfather ?? '') . ' ' . ($row->last_name ?? ''))) }}
                            </td>
                            <td>{{ e($row->branch ?? '') }}</td>
                            <td>{{ e($row->major ?? '') }}</td>
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
