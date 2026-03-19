<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dashboard-layout">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('meta')
    <title>@yield('title', config('app.name'))</title>
    {{-- تحميل تنسيقات الداشبورد دائماً من public لضمان ظهورها --}}
    <link rel="stylesheet" href="{{ url('css/dashboard.css') }}?v={{ file_exists(public_path('css/dashboard.css')) ? filemtime(public_path('css/dashboard.css')) : time() }}">
    @yield('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="@yield('icon', asset('favicon.svg'))">
</head>
<body class="dashboard-layout @yield('body_class')">
    <div class="dashboard-wrap">
        <aside class="dashboard-sidebar" aria-label="القائمة الجانبية">
            {{-- أيقونة الداشبورد / الصفحة الرئيسية --}}
            <a href="{{ route('dashboard') }}" aria-label="الصفحة الرئيسية" style="margin-top: 1rem;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 11L12 3l9 8" />
                    <path d="M5 13v8h5v-5h4v5h5v-8" />
                </svg>
                <span>الصفحة الرئيسية</span>
            </a>
            {{-- بيانات الطلبة --}}
            <a href="{{ $students_index_url ?? route('students.index') }}" aria-label="بيانات الطلبة" style="margin-top: 0.9rem;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>بيانات الطلبة</span>
            </a>
            <a href="{{ $students_bulk_print_url ?? route('students.documents.bulk-print') }}" aria-label="طباعة القيود" id="sidebar-link-bulk-print">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <span>القيود</span>
            </a>
            <a href="#" aria-label="ادخال النتائج">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                <span>ادخال النتائج</span>
            </a>
           
           
            <a href="#" aria-label="الراسبين" id="sidebar-link-failures">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <span>الراسبين</span>
            </a>
        </aside>

        <main class="dashboard-main">
            <header class="dashboard-toolbar" aria-label="شريط الأدوات">
                <div class="dashboard-toolbar-group">
                   
                    @hasSection('toolbar_actions')
                        @yield('toolbar_actions')
                    @endif
                    <div class="dashboard-toolbar-user">
                        <div class="dashboard-toolbar-user-avatar" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <span class="dashboard-toolbar-user-name">اسم المستخدم</span>
                    </div>
                    <a href="{{ route('employees.index') }}" class="dashboard-toolbar-employees" aria-label="الموظفون" title="الموظفون">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>الموظفون</span>
                    </a>
                    <a href="{{ route('students.create') }}" class="dashboard-toolbar-btn" aria-label="إضافة طالب" title="إضافة طالب">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" y1="8" x2="19" y2="14"/>
                            <line x1="22" y1="11" x2="16" y2="11"/>
                        </svg>
                        <span>إضافة طالب</span>
                    </a>
                    <a href="{{ route('students.import-excel') }}" class="dashboard-toolbar-btn" aria-label="استيراد اكسل" title="استيراد اكسل">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <path d="M8 13h2"/>
                            <path d="M8 17h2"/>
                            <path d="M14 13h2"/>
                            <path d="M14 17h2"/>
                        </svg>
                        <span>استيراد اكسل</span>
                    </a>
                </div>
            </header>
            <div class="dashboard-content">
                @yield('content')
            </div>
        </main>
    </div>

    @yield('scripts')
    <script>
        (function () {
            var link = document.getElementById('sidebar-link-bulk-print');
            if (!link || typeof link.href !== 'string') return;
            link.addEventListener('click', function (e) {
                try {
                    var url = new URL(link.href, window.location.origin);
                    var branch = (url.searchParams.get('branch') || '').trim();
                    var major = (url.searchParams.get('major') || '').trim();
                    var year = (url.searchParams.get('year') || '').trim();
                    var onBulkPage = window.location.pathname.indexOf('/documents/print') !== -1;
                    if (!onBulkPage && (branch === '' || major === '' || year === '')) {
                        e.preventDefault();
                        alert('يرجى تحديد الفرع والاختصاص والعام الدراسي من الفلاتر أولاً، ثم النقر على «القيود».');
                    }
                } catch (err) {}
            });
        })();
    </script>
</body>
</html>

