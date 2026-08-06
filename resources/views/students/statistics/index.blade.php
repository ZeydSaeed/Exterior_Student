@extends('layouts.dashboard')

@section('title', 'الاحصائيات')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-students page-students-statistics')

@section('toolbar_center')
    @include('students.partials.toolbar-filter-summary', ['useStudentListSessionMerge' => true])
@endsection

@section('content')
    @include('students.partials.filters', [
        'students_filter_form_action' => route('students.statistics.index'),
        'useStudentListSessionMerge' => true,
    ])

    <div class="students-layout">
        <section class="students-table-area" aria-label="لوحة إحصائيات الطلبة">
            @include('students.statistics.partials.summary')
        </section>
    </div>
@endsection
