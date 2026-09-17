@extends('layouts.dashboard')

@section('title', 'نظام إدارة الطلبة الخريجون')
@section('icon', asset('favicon-dashboard.svg'))

@section('content')
    <div class="dashboard-home">
        <img class="dashboard-moe-badge dashboard-moe-badge-start" src="{{ asset('images/moe-vocational-logo.png') }}?v={{ filemtime(public_path('images/moe-vocational-logo.png')) }}" alt="وزارة التربية — المديرية العامة للتعليم المهني" width="148" height="148">
        <img class="dashboard-moe-badge dashboard-moe-badge-end" src="{{ asset('images/moe-iraq-seal.png') }}?v={{ filemtime(public_path('images/moe-iraq-seal.png')) }}" alt="وزارة التربية — جمهورية العراق" width="153" height="148">
        <div class="app-title-brand">
            <img src="{{ asset('images/ems-logo.svg') }}" alt="EMS" class="app-title-logo" width="72" height="72">
            <div class="app-title-copy">
                <h1 class="app-title-text">نظام إدارة الطلبة الخريجون</h1>
                <p class="app-title-subtitle">External Students Management System</p>
            </div>
        </div>
        @php
            $heroImg = \Illuminate\Support\Facades\File::exists(public_path('images/19934.svg'))
                ? 'images/19934.svg'
                : (\Illuminate\Support\Facades\File::exists(public_path('images/19934.png'))
                    ? 'images/19934.png'
                    : 'images/19934.jpg');
        @endphp
        <img class="dashboard-hero-img" src="{{ asset($heroImg) }}" alt="???? ?????????" />
        <footer class="dashboard-home-footer" aria-label="معلومات المصمم">
            <p class="dashboard-home-footer-credit" dir="ltr">Designed &amp; Developed by Dr. Zeyd Saeed</p>
            <div class="dashboard-home-footer-contacts">
                <a class="dashboard-home-footer-icon dashboard-home-footer-icon-whatsapp" href="https://wa.me/9647805047871" target="_blank" rel="noopener noreferrer" aria-label="واتساب" title="واتساب">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M12.04 2C6.5 2 2.02 6.48 2.02 12.02c0 1.77.46 3.45 1.28 4.91L2 22l5.2-1.26c1.41.77 3.02 1.21 4.84 1.21h.01c5.54 0 10.02-4.48 10.02-10.02C22.07 6.48 17.58 2 12.04 2zm5.43 14.38c-.23.64-1.34 1.18-1.85 1.25-.47.07-1.08.1-1.74-.11-.4-.12-.91-.28-1.57-.55-2.76-1.19-4.56-3.97-4.7-4.16-.14-.19-1.13-1.5-1.13-2.86 0-1.36.71-2.03.96-2.31.25-.28.54-.35.72-.35h.52c.16 0 .38-.06.59.45.23.54.77 1.87.84 2 .07.14.11.3.02.48-.09.19-.14.3-.27.46-.13.16-.28.36-.4.48-.13.14-.26.29-.11.54.15.25.65 1.08 1.4 1.75 1.07.96 1.82 1.27 2.08 1.41.26.14.41.12.56-.07.15-.19.65-.76.83-1.02.17-.26.35-.22.59-.13.24.09 1.52.72 1.78.85.26.13.43.2.5.31.07.11.07.64-.16 1.28z"/>
                    </svg>
                </a>
                <a class="dashboard-home-footer-icon dashboard-home-footer-icon-telegram" href="https://t.me/+9647805047871" target="_blank" rel="noopener noreferrer" aria-label="تليغرام" title="تليغرام">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M21.9 4.3c.3-.9-.5-1.6-1.4-1.3L2.7 9.3c-.9.3-.9 1.6.1 1.9l4.6 1.5 1.8 5.7c.2.7 1.1.9 1.6.4l2.6-2.5 4.5 3.3c.7.5 1.7.1 1.9-.7l2.1-14.6zM9.3 13.5l8.4-5.3c.2-.1.4.2.2.3l-6.9 6.3-.4 2.4-1.3-3.7z"/>
                    </svg>
                </a>
                <a class="dashboard-home-footer-icon dashboard-home-footer-icon-viber" href="viber://chat?number=%2B9647805047871" aria-label="فايبر" title="فايبر">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M18.7 14.4c-1.2 0-2.3-.2-3.4-.6-.4-.1-.8 0-1.1.3l-1.5 1.8c-2.3-1.2-4.2-3.1-5.4-5.4l1.8-1.5c.3-.3.4-.7.3-1.1-.4-1.1-.6-2.2-.6-3.4 0-.6-.5-1.1-1.1-1.1H4.4c-.6 0-1.1.5-1.1 1.1 0 8 6.5 14.5 14.5 14.5.6 0 1.1-.5 1.1-1.1v-3.3c0-.6-.5-1.1-1.1-1.1h-.1z"/>
                    </svg>
                </a>
                <a class="dashboard-home-footer-phone" href="tel:+9647805047871" dir="ltr" aria-label="رقم الموبايل 07805047871">07805047871</a>
            </div>
            <a class="dashboard-home-footer-email" href="mailto:zeydsaeed@gmail.com" dir="ltr" aria-label="البريد الإلكتروني">
                <span class="dashboard-home-footer-icon dashboard-home-footer-icon-email" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5L4 8V6l8 5 8-5v2z"/>
                    </svg>
                </span>
                <span>zeydsaeed@gmail.com</span>
            </a>
        </footer>
    </div>
@endsection

