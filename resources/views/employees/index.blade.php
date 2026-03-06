@extends('layouts.dashboard')

@section('title', 'إدارة الموظفين')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-employees')

@section('meta')
    <meta name="employees-signatures-url" content="{{ route('employees.signatures.store') }}">
@endsection

@section('content')
    <div class="employees-page-header">
        <h1>إعدادات الموظفين</h1>
    </div>

    @if(session('success'))
        <p class="employees-success">{{ session('success') }}</p>
    @endif

    @php
        $table1Employees = collect($employees)->where('table_group', 1)->values()->all();
        $table2Employees = collect($employees)->where('table_group', 2)->values()->all();
    @endphp

    <div class="students-layout">
        <section class="students-table-area" aria-label="إعدادات الموظفين" data-signatures-url="{{ route('employees.signatures.store') }}">
            {{-- كارت الجدول الأول: العنوان الأيمن للتأييد --}}
            <div class="employees-card employees-card-right">
                <h2 class="employees-card-title">العنوان الأيمن للتأييد</h2>
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
                            @forelse($table1Employees as $employee)
                                <tr>
                                    <td>
                                        <input type="radio"
                                            name="right_signature"
                                            value="{{ $employee['id'] }}"
                                            id="right_{{ $employee['id'] }}"
                                            {{ (isset($right_selected) && (int) $right_selected === (int) $employee['id']) ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('employees.update', $employee['id']) }}" class="inline-form">
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
                                        <form method="POST" action="{{ route('employees.destroy', $employee['id']) }}" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-primary btn-delete-row">حذف</button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">لا يوجد موظفون في هذا الجدول.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="employees-card-add">
                    <h3 class="employees-add-title">إضافة موظف جديد</h3>
                    <form method="POST" action="{{ route('employees.store') }}" class="students-search-form">
                        @csrf
                        <input type="hidden" name="table_group" value="1" />
                        <div class="students-search">
                            <input type="text" name="type" placeholder="النوع..." required />
                            <input type="text" name="name" placeholder="اسم الموظف..." required />
                            <button type="submit" class="btn-primary">إضافة</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- كارت الجدول الثاني: العنوان الأيسر للتأييد --}}
            <div class="employees-card employees-card-left">
                <h2 class="employees-card-title">العنوان الأيسر للتأييد</h2>
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
                            @forelse($table2Employees as $employee)
                                <tr>
                                    <td>
                                        <input type="radio"
                                            name="left_signature"
                                            value="{{ $employee['id'] }}"
                                            id="left_{{ $employee['id'] }}"
                                            {{ (isset($left_selected) && (int) $left_selected === (int) $employee['id']) ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('employees.update', $employee['id']) }}" class="inline-form">
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
                                        <form method="POST" action="{{ route('employees.destroy', $employee['id']) }}" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-primary btn-delete-row">حذف</button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">لا يوجد موظفون في هذا الجدول.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="employees-card-add">
                    <h3 class="employees-add-title">إضافة موظف جديد</h3>
                    <form method="POST" action="{{ route('employees.store') }}" class="students-search-form">
                        @csrf
                        <input type="hidden" name="table_group" value="2" />
                        <div class="students-search">
                            <input type="text" name="type" placeholder="النوع..." required />
                            <input type="text" name="name" placeholder="اسم الموظف..." required />
                            <button type="submit" class="btn-primary">إضافة</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
    (function () {
        var url = '{{ url(route("employees.signatures.store")) }}';
        var token = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        function saveSignatures() {
            var right = document.querySelector('input[name="right_signature"]:checked');
            var left = document.querySelector('input[name="left_signature"]:checked');
            var body = new FormData();
            body.append('_token', token);
            body.append('right_signature', (right && right.value) ? right.value : '');
            body.append('left_signature', (left && left.value) ? left.value : '');
            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: body
            }).catch(function () {});
        }
        document.querySelectorAll('input[name="right_signature"]').forEach(function (r) { r.addEventListener('change', saveSignatures); });
        document.querySelectorAll('input[name="left_signature"]').forEach(function (r) { r.addEventListener('change', saveSignatures); });
    })();
    </script>
    <script src="{{ asset('js/employees.js') }}"></script>
@endsection
