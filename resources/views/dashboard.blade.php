@extends('layouts.dashboard')

@section('title', 'نظام إدارة الطلبة الخارجيون')
@section('icon', asset('favicon-dashboard.svg'))

@section('content')
    <p>نظام إدارة الطلبة الخارجيون</p>
    @php
        $heroImg = \Illuminate\Support\Facades\File::exists(public_path('images/19934.svg'))
            ? 'images/19934.svg'
            : (\Illuminate\Support\Facades\File::exists(public_path('images/19934.png'))
                ? 'images/19934.png'
                : 'images/19934.jpg');
    @endphp
    <img class="dashboard-hero-img" src="{{ asset($heroImg) }}" alt="???? ?????????" />
@endsection

