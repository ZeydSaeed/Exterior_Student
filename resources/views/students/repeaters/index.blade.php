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
                <div class="repeaters-group-meta" style="margin-bottom: .75rem;">
                    <strong>الفرع:</strong> {{ $group['branch'] !== '' ? $group['branch'] : 'غير محدد' }}
                    <span style="display:inline-block; margin-inline: .5rem;">|</span>
                    <strong>الاختصاص:</strong> {{ $group['major'] !== '' ? $group['major'] : 'غير محدد' }}
                    <span style="display:inline-block; margin-inline: .5rem;">|</span>
                    <strong>العام الدراسي:</strong> {{ $selectedYear ?? request('year') ?? 'غير محدد' }}
                    <!-- <span style="display:inline-block; margin-inline: .5rem;">|</span> -->
                    <!-- <strong>عدد الطلبة المعيدين:</strong> {{ $group['count'] }} -->
                </div>

                <div class="students-table-wrapper" style="margin-bottom: 1rem;">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الرقم الامتحاني</th>
                                <th>اسم الطالب</th>
@php
    $subjectColumns = [];
    foreach ($group['students'] as $studTmp) {
        foreach ($studTmp['subjects'] as $subTmp) {
            $name = (string) ($subTmp['subject'] ?? '');
            $rawScore = $subTmp['score'] ?? null;
            if (! is_numeric($rawScore) || (float) $rawScore >= 50) {
                continue;
            }
            if ($name === '') {
                continue;
            }
            if (! in_array($name, $subjectColumns, true)) {
                $subjectColumns[] = $name;
            }
        }
    }
@endphp
@foreach($subjectColumns as $subjectName)
                                <th>{{ $subjectName }}</th>
@endforeach
                                <th>النتيجة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($group['students'] as $student)
@php
    $scoresBySubject = [];
    foreach ($student['subjects'] as $subRow) {
        $subjectName = (string) ($subRow['subject'] ?? '');
        $rawScore = $subRow['score'] ?? null;
        if ($subjectName === '' || ! is_numeric($rawScore) || (float) $rawScore >= 50) {
            continue;
        }
        $scoresBySubject[$subjectName] = $subRow['score'] ?? '';
    }
@endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $student['exam_number'] }}</td>
                                    <td>{{ $student['full_name'] }}</td>
@foreach($subjectColumns as $subjectName)
                                    <td>{{ $scoresBySubject[$subjectName] ?? '' }}</td>
@endforeach
                                    <td>{{ $student['result'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 4 + count($subjectColumns) }}">لا توجد بيانات معيدين ضمن هذا الجدول.</td>
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
