<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'طباعة القيود')</title>
    <link rel="stylesheet" href="{{ url('css/student-document.css') }}?v={{ file_exists(public_path('css/student-document.css')) ? filemtime(public_path('css/student-document.css')) : time() }}">
    <style>
        .doc-bulk-print-actions { padding: 0.5rem; text-align: center; }
        .doc-bulk-page-break { page-break-after: always; break-after: page; }
        .doc-bulk-page-break:last-child { page-break-after: auto; break-after: auto; }
    </style>
</head>
<body class="page-student-document">
    <div class="doc-bulk-print-actions no-print">
        <button type="button" class="btn-primary" onclick="window.print()">طباعة</button>
        <a href="{{ \App\Support\StudentListFiltersSession::indexUrl(request()) }}" class="btn-primary btn-close">إغلاق</a>
    </div>
    @yield('content')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.location.search.indexOf('autoprint=1') !== -1) {
                setTimeout(function () { window.print(); }, 300);
            }
        });
    </script>
</body>
</html>
