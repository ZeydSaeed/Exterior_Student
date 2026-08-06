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
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="@yield('icon', asset('favicon-students.svg'))">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="application-name" content="نظام الطلبة">
    <meta name="theme-color" content="#4a545e">
</head>
<body class="dashboard-layout @yield('body_class')">
    <div class="dashboard-wrap">
        <aside class="dashboard-sidebar" aria-label="القائمة الجانبية">
            {{-- أيقونة الداشبورد / الصفحة الرئيسية --}}
            <a href="{{ route('dashboard') }}" class="dashboard-sidebar-link @if(request()->routeIs('dashboard')) is-active @endif" aria-label="الصفحة الرئيسية" @if(request()->routeIs('dashboard')) aria-current="page" @endif style="margin-top: 0rem;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 11L12 3l9 8" />
                    <path d="M5 13v8h5v-5h4v5h5v-8" />
                </svg>
                <span>الصفحة الرئيسية</span>
            </a>
            {{-- بيانات الطلبة --}}
            <a href="{{ $students_index_url ?? route('students.index') }}" class="dashboard-sidebar-link @if(request()->routeIs('students.index')) is-active @endif" aria-label="بيانات الطلبة" @if(request()->routeIs('students.index')) aria-current="page" @endif style="margin-top: 0.9rem;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>بيانات الطلبة</span>
            </a>
            <a href="{{ $students_bulk_print_url ?? route('students.documents.bulk-print') }}" class="dashboard-sidebar-link @if(request()->routeIs('students.documents.bulk-print')) is-active @endif" aria-label="طباعة القيود" id="sidebar-link-bulk-print" @if(request()->routeIs('students.documents.bulk-print')) aria-current="page" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <span>القيود</span>
            </a>
            <a href="{{ route('students.import-excel') }}" class="dashboard-sidebar-link @if(request()->routeIs('students.import-excel') || request()->routeIs('students.import-excel.*')) is-active @endif" aria-label="استيراد بيانات الطلبة من اكسل" @if(request()->routeIs('students.import-excel') || request()->routeIs('students.import-excel.*')) aria-current="page" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <path d="M8 13h2"/>
                    <path d="M8 17h2"/>
                    <path d="M14 13h2"/>
                    <path d="M14 17h2"/>
                </svg>
                <span>استيراد بيانات الطلبة من اكسل</span>
            </a>
            <a href="{{ route('students.results-import-excel') }}" class="dashboard-sidebar-link @if(request()->routeIs('students.results-import-excel') || request()->routeIs('students.results-import-excel.*')) is-active @endif" aria-label="ادخال النتائج" @if(request()->routeIs('students.results-import-excel') || request()->routeIs('students.results-import-excel.*')) aria-current="page" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                <span> استيراد النتائج من اكسل</span>
            </a>
           
           
            <a href="#" class="dashboard-sidebar-link" aria-label="الراسبين" id="sidebar-link-failures">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <span>الراسبين</span>
            </a>
            <a href="{{ route('students.repeaters.index') }}" class="dashboard-sidebar-link @if(request()->routeIs('students.repeaters.index')) is-active @endif" aria-label="المعيدين" id="sidebar-link-repeaters" @if(request()->routeIs('students.repeaters.index')) aria-current="page" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12a9 9 0 1 1-2.64-6.36"/>
                    <polyline points="21 3 21 9 15 9"/>
                    <path d="M12 7v5l3 2"/>
                </svg>
                <span>المعيدين</span>
            </a>
            <a href="{{ $students_statistics_url ?? route('students.statistics.index') }}" class="dashboard-sidebar-link @if(request()->routeIs('students.statistics.index') || request()->routeIs('students.statistics.*')) is-active @endif" aria-label="الاحصائيات" id="sidebar-link-statistics" @if(request()->routeIs('students.statistics.index') || request()->routeIs('students.statistics.*')) aria-current="page" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                <span>الاحصائيات</span>
            </a>
        </aside>

        <main class="dashboard-main">
            <header class="dashboard-toolbar" aria-label="شريط الأدوات">
                <div class="dashboard-toolbar-row">
                    <div class="dashboard-toolbar-slot dashboard-toolbar-slot-start">
                        <div class="dashboard-toolbar-group">
                    <!-- <div class="dashboard-toolbar-user">
                        <div class="dashboard-toolbar-user-avatar" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <span class="dashboard-toolbar-user-name">اسم المستخدم</span>
                    </div> -->
                    <a href="{{ route('employees.index') }}" class="dashboard-toolbar-employees @if(request()->routeIs('employees.*')) is-active @endif" aria-label="الموظفون" title="الموظفون" @if(request()->routeIs('employees.*')) aria-current="page" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>الموظفون</span>
                    </a>
                    <a href="{{ route('students.create') }}" class="dashboard-toolbar-btn @if(request()->routeIs('students.create')) is-active @endif" aria-label="إضافة طالب" title="إضافة طالب" @if(request()->routeIs('students.create')) aria-current="page" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" y1="8" x2="19" y2="14"/>
                            <line x1="22" y1="11" x2="16" y2="11"/>
                        </svg>
                        <span>إضافة طالب</span>
                    </a>
                    <form method="POST" action="{{ route('database-backup.store') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="dashboard-toolbar-btn" aria-label="نسخ احتياطي" title="نسخ احتياطي" style="background: transparent; border: none; padding: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <path d="M12 15V3"/>
                            </svg>
                            <span>نسخ احتياطي</span>
                        </button>
                    </form>
                        </div>
                    </div>
                    <div class="dashboard-toolbar-slot dashboard-toolbar-slot-center">
                        @hasSection('toolbar_center')
                            @yield('toolbar_center')
                        @endif
                    </div>
                    <div class="dashboard-toolbar-slot dashboard-toolbar-slot-end">
                        @hasSection('toolbar_actions')
                            @yield('toolbar_actions')
                        @endif
                    </div>
                </div>
            </header>
            <div class="dashboard-content">
                @yield('content')
            </div>
        </main>
    </div>

    @unless(request()->routeIs('students.certificate'))
        @include('partials.app-error-dialog')
        <script src="{{ url('js/app-error-dialog.js') }}?v={{ file_exists(public_path('js/app-error-dialog.js')) ? filemtime(public_path('js/app-error-dialog.js')) : time() }}"></script>
    @endunless

    @yield('scripts')
</body>
</html>

