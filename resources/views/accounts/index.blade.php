@extends('layouts.dashboard')

@section('title', 'الحسابات والصلاحيات')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-accounts')

@section('content')
    <div class="employees-page-wrap accounts-page-wrap">
        <a href="{{ route('dashboard') }}" class="btn-primary employees-close-btn" title="إغلاق">إغلاق</a>

        <div class="employees-page-header">
            <h1>الحسابات والصلاحيات</h1>
        </div>

        @if(session('success'))
            <p class="employees-success">{{ session('success') }}</p>
        @endif

        <div class="students-layout">
            <section class="students-table-area" aria-label="إدارة الحسابات">
                <div class="employees-card">
                    <h2 class="employees-card-title">الحسابات الحالية</h2>
                    <div class="students-table-wrapper accounts-table-wrapper">
                        <table class="students-table accounts-table">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>اسم الدخول</th>
                                    <th>النوع</th>
                                    <th>كلمة المرور</th>
                                    <th>الصلاحيات</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $account)
                                    <tr>
                                        <form method="POST" action="{{ route('accounts.update', $account['id']) }}" class="accounts-edit-form">
                                            @csrf
                                            @method('PUT')
                                            <td>
                                                <input type="text" name="name" value="{{ $account['name'] }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="username" value="{{ $account['username'] }}" required minlength="3">
                                            </td>
                                            <td>
                                                <label class="accounts-admin-check">
                                                    <input type="checkbox" name="is_admin" value="1" @checked($account['is_admin']) class="accounts-is-admin-toggle">
                                                    <span>مسؤول</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="accounts-password-field">
                                                    <input
                                                        type="password"
                                                        name="password"
                                                        class="accounts-password-input"
                                                        value="{{ $account['password_display'] ?? '' }}"
                                                        placeholder="{{ ($account['password_display'] ?? null) ? '' : 'أدخل كلمة مرور ثم احفظ' }}"
                                                        minlength="6"
                                                        autocomplete="new-password"
                                                    >
                                                    <button type="button" class="accounts-password-toggle" aria-label="إظهار كلمة المرور" title="إظهار كلمة المرور" aria-pressed="false">
                                                        <svg class="accounts-password-icon-hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                            <circle cx="12" cy="12" r="3"/>
                                                            <line x1="6" y1="7" x2="18" y2="17"/>
                                                        </svg>
                                                        <svg class="accounts-password-icon-visible" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                            <circle cx="12" cy="12" r="3"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="accounts-permissions-cell">
                                                <details class="accounts-permissions-details" @if($account['is_admin']) hidden @endif>
                                                    <summary>تحديد الصلاحيات ({{ count($account['permissions']) }})</summary>
                                                    <div class="accounts-permissions-grid">
                                                        @foreach($permission_groups as $groupKey => $group)
                                                            <fieldset class="accounts-permission-group">
                                                                <legend>{{ $group['label'] }}</legend>
                                                                @foreach($group['permissions'] as $permKey => $permLabel)
                                                                    <label class="accounts-permission-item">
                                                                        <input
                                                                            type="checkbox"
                                                                            name="permissions[]"
                                                                            value="{{ $permKey }}"
                                                                            @checked(in_array($permKey, $account['permissions'], true))
                                                                        >
                                                                        <span>{{ $permLabel }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </fieldset>
                                                        @endforeach
                                                    </div>
                                                </details>
                                                @if($account['is_admin'])
                                                    <span class="accounts-admin-all">كل الصلاحيات</span>
                                                @endif
                                            </td>
                                            <td class="students-table-actions">
                                                <div class="students-table-actions-inner">
                                                    <button type="submit" class="btn-primary btn-edit-row">حفظ</button>
                                        </form>
                                                    <form method="POST" action="{{ route('accounts.destroy', $account['id']) }}" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-primary btn-delete-row">حذف</button>
                                                    </form>
                                                </div>
                                            </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">لا توجد حسابات.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="employees-card employees-card-add">
                    <h3 class="employees-add-title">إضافة حساب جديد</h3>
                    <form method="POST" action="{{ route('accounts.store') }}" class="accounts-create-form">
                        @csrf
                        <div class="accounts-create-fields">
                            <input type="text" name="name" placeholder="الاسم المعروض..." value="{{ old('name') }}" required>
                            <input type="text" name="username" placeholder="اسم الدخول..." value="{{ old('username') }}" required minlength="3" autocomplete="off">
                            <div class="accounts-password-field accounts-password-field-create">
                                <input type="password" name="password" class="accounts-password-input" placeholder="كلمة المرور..." required minlength="6" autocomplete="new-password">
                                <button type="button" class="accounts-password-toggle" aria-label="إظهار كلمة المرور" title="إظهار كلمة المرور" aria-pressed="false">
                                    <svg class="accounts-password-icon-hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                        <line x1="6" y1="7" x2="18" y2="17"/>
                                    </svg>
                                    <svg class="accounts-password-icon-visible" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <label class="accounts-admin-check">
                                <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin')) id="accounts-create-is-admin">
                                <span>مسؤول (كل الصلاحيات)</span>
                            </label>
                        </div>
                        <div class="accounts-permissions-grid" id="accounts-create-permissions">
                            @foreach($permission_groups as $groupKey => $group)
                                <fieldset class="accounts-permission-group">
                                    <legend>{{ $group['label'] }}</legend>
                                    @foreach($group['permissions'] as $permKey => $permLabel)
                                        <label class="accounts-permission-item">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permKey }}"
                                                @checked(is_array(old('permissions')) && in_array($permKey, old('permissions'), true))
                                            >
                                            <span>{{ $permLabel }}</span>
                                        </label>
                                    @endforeach
                                </fieldset>
                            @endforeach
                        </div>
                        <button type="submit" class="btn-primary">إضافة الحساب</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    function syncAdminPermissions(toggle, details, allLabel) {
        if (!toggle) return;
        var isAdmin = toggle.checked;
        if (details) {
            details.hidden = isAdmin;
        }
        if (allLabel) {
            allLabel.hidden = !isAdmin;
        }
    }

    document.querySelectorAll('.accounts-edit-form').forEach(function (form) {
        var toggle = form.querySelector('.accounts-is-admin-toggle');
        var row = form.closest('tr');
        var details = row ? row.querySelector('.accounts-permissions-details') : null;
        var allLabel = row ? row.querySelector('.accounts-admin-all') : null;
        if (toggle) {
            toggle.addEventListener('change', function () {
                syncAdminPermissions(toggle, details, allLabel);
            });
        }
    });

    var createToggle = document.getElementById('accounts-create-is-admin');
    var createPerms = document.getElementById('accounts-create-permissions');
    if (createToggle && createPerms) {
        createToggle.addEventListener('change', function () {
            createPerms.hidden = createToggle.checked;
        });
        createPerms.hidden = createToggle.checked;
    }

    document.querySelectorAll('.accounts-password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = btn.closest('.accounts-password-field');
            var input = wrap ? wrap.querySelector('.accounts-password-input') : null;
            if (!input) return;

            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            btn.setAttribute('aria-label', isHidden ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            btn.setAttribute('title', isHidden ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
        });
    });
})();
</script>
@endsection
