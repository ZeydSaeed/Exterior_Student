@extends('layouts.dashboard')

@section('title', 'تأييد بدون درجات')
@section('body_class', 'page-certificate')

@section('styles')
    <link rel="stylesheet" href="{{ url('css/certificate.css') }}?v={{ file_exists(public_path('css/certificate.css')) ? filemtime(public_path('css/certificate.css')) : time() }}">
@endsection

@section('content')
    @php
        $organizer = $dto->employees[0]['name'] ?? 'غير محدد';
        $organizerTitle = $dto->employees[0]['type'] ?? 'منظم التأييد';
        $manager = $dto->employees[1]['name'] ?? 'غير محدد';
        $managerTitle = $dto->employees[1]['type'] ?? 'مسؤول شعبة شؤون الطلبة';
    @endphp

    <div class="students-layout">
        <section class="students-table-area" aria-label="تأييد بدون درجات">
            <div class="certificate-page">
                <div class="certificate-fit-wrapper">
                    <div class="support-paper print-area" contenteditable="true">
                        <div class="form-actions no-print" contenteditable="false">
                            <button type="button" class="btn-primary btn-print" id="certificate-btn-print">طباعة</button>
                            <a href="{{ route('students.index') }}" class="btn-primary btn-close">إغلاق</a>
                        </div>

                        <div class="top-line">جمهورية العراق</div>
                        <div class="right-line">وزارة التربية</div>
                        <div class="right-line2">قسم التعليم المهني / كربلاء المقدسة</div>
                        <div class="right-line2">المدرسة: خارجيون</div>

                        <div class="photo-frame">صورة الطالب</div>

                        <div class="meta-line">العدد: <span class="editable arabic-number" contenteditable="true"></span></div>
                        <div class="meta-line">التاريخ: <span class="editable arabic-date" contenteditable="true" data-date="{{ now()->format('Y-m-d') }}">{{ now()->format('d / m / Y') }}</span></div>
                        <div class="meta-line">الرقم الامتحاني: <span class="arabic-number" data-number="{{ $dto->examNumber }}">{{ $dto->examNumber }}</span></div>

                        <br>

                        <div class="meta-line to-line">
                            <strong>الى /</strong>
                            <span class="editable" contenteditable="true"></span>
                        </div>

                        <div class="subject-line">
                            <strong>الموضوع / تأييد</strong>
                        </div>

                        <div class="body-line" contenteditable="true">
                            نؤيد لكم أن الطالب ({{ $dto->fullName }}) الملصقة صورته أعلاه، والمولود بتاريخ <span class="nowrap arabic-date" data-date="{{ $dto->birthDate }}">({{ $dto->birthDate }})</span> أحد طلاب الصف الثالث إعدادي مهني، الفرع ({{ $dto->branch }}) / الاختصاص <span class="nowrap">({{ $dto->specialization }})</span>، اشترك بالامتحانات الوزارية للعام الدراسي <span class="nowrap arabic-number" data-number="{{ $dto->academicYear }}">({{ $dto->academicYear }})</span> وكانت النتيجة ({{ $dto->result }}) في الدور ({{ $dto->round }})، وبناءً على طلبه زُوِّد بهذا التأييد.
                        </div>

                        <div class="signature-space"></div>

                        <div class="signature-columns">
                            <div class="signature-col">
                                <div class="signature-title">{{ $organizerTitle }}</div>
                                <div class="signature-name">{{ $organizer }}</div>
                                <div class="signature-date arabic-date" data-date="{{ now()->format('Y-m-d') }}">{{ now()->format('Y-m-d') }}</div>
                            </div>
                            <div class="signature-col">
                                <div class="signature-title">{{ $managerTitle }}</div>
                                <div class="signature-name">{{ $manager }}</div>
                                <div class="signature-date arabic-date" data-date="{{ now()->format('Y-m-d') }}">{{ now()->format('Y-m-d') }}</div>
                            </div>
                        </div>

                        <div class="footer-note">
                            * التأييد خالٍ من الحك والشطب والتحريف.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script src="{{ url('js/arabic-date.js') }}?v={{ file_exists(public_path('js/arabic-date.js')) ? filemtime(public_path('js/arabic-date.js')) : time() }}"></script>
    <script src="{{ url('js/students/certificate.js') }}?v={{ file_exists(public_path('js/students/certificate.js')) ? filemtime(public_path('js/students/certificate.js')) : time() }}"></script>
@endsection
