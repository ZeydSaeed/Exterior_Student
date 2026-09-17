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
    </div>
@endsection

