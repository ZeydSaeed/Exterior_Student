@extends('layouts.dashboard')

@section('title', 'إدارة الموظفين')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-employees')
@section('meta')
    <meta name="session-url" content="{{ route('employees.selected-to-session') }}">
@endsection

@section('content')
    <h1>اعدادات الموظفين</h1>

    @php
        $typeLabels = [
            'organizer' => 'منظم التأييد',
            'manager' => 'مسؤول شعبة شؤون الطلبة',
        ];
    @endphp

    <div class="students-layout">
        <section class="students-table-area" aria-label="إعدادات الموظفين">
            <div class="students-table-wrapper">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>اختيار</th>
                            <th>النوع</th>
                            <th>الاسم</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <input type="checkbox" class="employee-select" value="{{ $employee['id'] }}" />
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('employees.update', $employee['id']) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="type" value="{{ $employee['type'] }}" />
                                </td>
                                <td>
                                        <input type="text" name="name" value="{{ $employee['name'] }}" />
                                </td>
                                <td class="students-table-actions">
                                    <div class="students-table-actions-inner">
                                        <button type="submit" class="btn-primary btn-edit-row">حفظ</button>
                                    </form>
                                    <form method="POST" action="{{ route('employees.destroy', $employee['id']) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-primary btn-delete-row" onclick="return confirm('هل أنت متأكد من الحذف؟');">
                                            حذف
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">لا توجد بيانات موظفين حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <hr style="margin: 1rem 0;">

            <h2 style="font-size: 1rem; margin-bottom: 0.5rem;">إضافة موظف جديد</h2>
            <form method="POST" action="{{ route('employees.store') }}" class="students-search-form">
                @csrf
                <div class="students-search">
                    <input type="text" name="type" placeholder="النوع..." required />
                    <input type="text" name="name" placeholder="اسم الموظف..." required />
                    <button type="submit" class="btn-primary">إضافة</button>
                </div>
            </form>
        </section>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/employees.js') }}"></script>
@endsection

