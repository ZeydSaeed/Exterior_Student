<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول — نظام إدارة الطلبة</title>
    <link rel="stylesheet" href="{{ url('css/dashboard.css') }}?v={{ file_exists(public_path('css/dashboard.css')) ? filemtime(public_path('css/dashboard.css')) : time() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icon-students-16.png') }}?v={{ file_exists(public_path('icon-students-16.png')) ? filemtime(public_path('icon-students-16.png')) : time() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icon-students-32.png') }}?v={{ file_exists(public_path('icon-students-32.png')) ? filemtime(public_path('icon-students-32.png')) : time() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-mark.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ file_exists(public_path('favicon.ico')) ? filemtime(public_path('favicon.ico')) : time() }}">
</head>
<body class="login-page">
    <div class="login-shell">
        <div class="login-card">
            <div class="login-brand-wrap">
                <img src="{{ asset('images/ems-logo.svg') }}" alt="EMS" class="login-brand-logo" width="88" height="88">
                <h1 class="login-brand">نظام إدارة الطلبة</h1>
            </div>
            <p class="login-subtitle">سجّل الدخول للمتابعة إلى لوحة التحكم</p>

            @if ($errors->any())
                <div class="login-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="login-form" autocomplete="on">
                @csrf
                <label class="login-label" for="username">اسم الدخول</label>
                <input
                    id="username"
                    class="login-input"
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    autocomplete="username"
                >

                <label class="login-label" for="password">كلمة المرور</label>
                <input
                    id="password"
                    class="login-input"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >

                <button type="submit" class="login-submit btn-primary">دخول</button>
            </form>
        </div>
    </div>
</body>
</html>
