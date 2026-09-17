@extends('layouts.dashboard')

@section('title', 'نظام إدارة الطلبة الخارجيون')
@section('icon', asset('favicon-dashboard.svg'))

@section('content')
            <div class="app-title-brand">
                <img src="{{ asset('images/ems-logo.svg') }}" alt="EMS" class="app-title-logo" width="72" height="72">
                <p>نظام إدارة الطلبة الخارجيون</p>
            </div>
    @php
        $heroImg = \Illuminate\Support\Facades\File::exists(public_path('images/19934.svg'))
            ? 'images/19934.svg'
            : (\Illuminate\Support\Facades\File::exists(public_path('images/19934.png'))
                ? 'images/19934.png'
                : 'images/19934.jpg');
    @endphp
    <img class="dashboard-hero-img" src="{{ asset($heroImg) }}" alt="???? ?????????" />
@endsection

