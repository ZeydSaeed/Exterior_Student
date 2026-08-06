@extends('layouts.dashboard')

@section('title', 'الطلبة المعيدين')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-students page-repeaters')

@section('toolbar_center')
    @include('students.partials.toolbar-filter-summary', ['useStudentListSessionMerge' => false])
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ url('css/repeaters-report-print.css') }}?v={{ file_exists(public_path('css/repeaters-report-print.css')) ? filemtime(public_path('css/repeaters-report-print.css')) : time() }}">
@endsection

@section('content')
    @include('students.partials.repeaters-filters')

    <div class="students-layout">
        <section class="students-table-area repeaters-report-print repeaters-print-area" aria-label="جدول الطلبة المعيدين">
            <div class="repeaters-print-actions no-print" style="display:flex;justify-content:flex-end;gap:0.5rem;margin-bottom:1rem;">
                <button type="button" class="btn-primary" onclick="window.print()">طباعة</button>
                <a href="{{ route('students.index') }}" class="btn-primary" style="text-decoration: none; font-weight: bold; font-family: 'Times New Roman', Times, serif;">إغلاق</a>
            </div>

            @if(!empty($yearRequiredError))
                <div class="flash-box flash-box-error" style="margin-bottom: .75rem;">
                    <span class="flash-text">{{ $yearRequiredError }}</span>
                </div>
            @endif

            @forelse($groups as $group)
                @php
                    $subjectColumns = $group['subject_columns'] ?? [];
                    $subjectCounts = $group['subject_repeater_counts'] ?? [];
                @endphp
                <div class="repeaters-group-meta" style="margin-bottom: .75rem;">
                    <strong>الفرع:</strong> {{ $group['branch'] !== '' ? $group['branch'] : 'غير محدد' }}
                    <span style="display:inline-block; margin-inline: .5rem;">|</span>
                    <strong>الاختصاص:</strong> {{ $group['major'] !== '' ? $group['major'] : 'غير محدد' }}
                    <span style="display:inline-block; margin-inline: .5rem;">|</span>
                    <strong>العام الدراسي:</strong> {{ $selectedYear ?? request('year') ?? 'غير محدد' }}
                </div>

                <div class="students-table-wrapper" style="margin-bottom: 1rem;">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم المادة</th>
                                <th>عدد الطلبة المعيدين</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjectColumns as $subjectName)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $subjectName }}</td>
                                    <td>{{ (int) ($subjectCounts[$subjectName] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">لا توجد مواد لهذا الاختصاص حسب الفلاتر الحالية.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="students-table-wrapper">
                    <table class="students-table">
                        <tbody>
                            <tr>
                                <td>لا توجد بيانات للطلبة المعيدين حسب الفلاتر الحالية.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforelse

            <div class="repeaters-total-meta" style="margin-top: .75rem; font-weight:700;">
                إجمالي الطلبة المعيدين : {{ $totalRepeaters }}
            </div>
        </section>
    </div>
@endsection
