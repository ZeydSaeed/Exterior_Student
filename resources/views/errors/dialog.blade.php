@extends('layouts.dashboard')

@section('title', $appDialog['title'] ?? 'خطأ')

@section('content')
    <div class="app-error-page-placeholder" aria-hidden="true"></div>
@endsection
