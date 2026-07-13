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
        $genderTrim = trim($dto->gender ?? '');
        $isFemale = in_array($genderTrim, ['انثى', 'أنثى'], true);
        $avgRaw = trim($dto->average ?? '');
        $avgInParens = $avgRaw !== '' ? \App\Support\ArabicDigits::toArabic($avgRaw) : '—';
    @endphp

    <div class="students-layout">
        <section class="students-table-area" aria-label="تأييد بدون درجات">
            <div class="certificate-page">
                <div class="certificate-fit-wrapper">
                    <div class="support-paper print-area" contenteditable="true">
                        <div class="form-actions no-print" contenteditable="false">
                            <button type="button" class="btn-primary btn-print" id="certificate-btn-print">طباعة</button>
                            <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary btn-close">إغلاق</a>
                        </div>
                        <form id="certificate-save-form" method="POST" action="{{ route('students.profile.attestations.store', $studentId) }}" style="display: none;">
                            @csrf
                            <input type="hidden" name="type" value="without_grades" />
                            <input type="text" name="date" id="cert-save-date" />
                            <input type="text" name="number" id="cert-save-number" />
                            <input type="text" name="issued_to" id="cert-save-issued-to" />
                            <input type="text" name="right_title" id="cert-save-right-title" />
                            <input type="text" name="right_employee_name" id="cert-save-right-employee" />
                            <input type="text" name="left_title" id="cert-save-left-title" />
                            <input type="text" name="left_employee_name" id="cert-save-left-employee" />
                        </form>

                        <div class="top-line">جمهورية العراق</div>
                        <div class="right-line">وزارة التربية</div>
                        <div class="right-line2">قسم التعليم المهني / كربلاء المقدسة</div>
                        <div class="right-line2">المدرسة: خارجيون</div>

                        <div class="photo-frame">@if($isFemale)صورة الطالبة@elseصورة الطالب@endif</div>

                        {{-- العدد: حقل نص قابل للتحرير؛ عند الطباعة تُقرأ القيمة وتُحفظ في certificate.number --}}
                        <div class="meta-line">العدد: <input type="text" id="cert-field-number" class="cert-field-input arabic-number" data-cert-db="number" placeholder="" /></div>
                        <div class="meta-line">التاريخ: <span id="cert-field-date" class="editable arabic-date" contenteditable="true" data-date="{{ now()->format('Y-m-d') }}">{{ now()->format('d / m / Y') }}</span></div>
                        <div class="meta-line">الرقم الامتحاني: <span class="arabic-number" data-number="{{ $dto->examNumber }}">{{ $dto->examNumber }}</span></div>

                        <br>

                        {{-- الى: حقل نص قابل للتحرير؛ عند الطباعة تُقرأ القيمة وتُحفظ في certificate.issued_to --}}
                        <div class="meta-line to-line">
                            <strong>الى /</strong>
                            <input type="text" id="cert-field-issued-to" class="cert-field-input cert-field-issued-to" data-cert-db="issued_to" placeholder="" />
                        </div>

                        <div class="subject-line">
                            <strong>الموضوع / تأييد</strong>
                        </div>

                        <div class="body-line" contenteditable="true">
                            @if($isFemale)
                            نؤيد لكم أن الطالبة ({{ $dto->fullName }}) الملصقة صورتها أعلاه، والمولودة بتاريخ <span class="nowrap arabic-date" data-date="{{ $dto->birthDate }}">({{ \App\Support\ImportDateNormalizer::toDisplayDmy($dto->birthDate) }})</span> إحدى طالبات الصف الثالث إعدادي مهني، الفرع ({{ $dto->branch }}) / الاختصاص <span class="nowrap">({{ $dto->specialization }})</span>، اشتركت بالامتحانات الوزارية للعام الدراسي <span class="nowrap arabic-number" data-number="{{ $dto->academicYear }}">({{ $dto->academicYear }})</span> وكانت نتيجتها ({{ $dto->result }}) في الدور ({{ $dto->round }}) وبمعدل ({{ $avgInParens }})، . وبناءً على طلبها زُوِّدَت بهذا التأييد. مع التقدير
                        @else
                            نؤيد لكم أن الطالب ({{ $dto->fullName }}) الملصقة صورته أعلاه، والمولود بتاريخ <span class="nowrap arabic-date" data-date="{{ $dto->birthDate }}">({{ \App\Support\ImportDateNormalizer::toDisplayDmy($dto->birthDate) }})</span> أحد طلاب الصف الثالث إعدادي مهني، الفرع ({{ $dto->branch }}) / الاختصاص <span class="nowrap">({{ $dto->specialization }})</span>، اشترك بالامتحانات الوزارية للعام الدراسي <span class="nowrap arabic-number" data-number="{{ $dto->academicYear }}">({{ $dto->academicYear }})</span> وكانت النتيجة ({{ $dto->result }}) في الدور ({{ $dto->round }}) وبمعدل ({{ $avgInParens }})، وبناءً على طلبه زُوِّد بهذا التأييد. مع التقدير
                            @endif
                        </div>

                        <div class="signature-space"></div>

                        <div class="signature-columns">
                            <div class="signature-col">
                                <div id="cert-field-right-name" class="signature-name">{{ $organizer }}</div>
                                <div id="cert-field-right-title" class="signature-title">{{ $organizerTitle }}</div>

                                <div class="signature-date arabic-date" data-date="{{ now()->format('Y-m-d') }}">{{ now()->format('Y-m-d') }}</div>
                            </div>
                            <div class="signature-col">
                                <div id="cert-field-left-name" class="signature-name">{{ $manager }}</div>
                                <div id="cert-field-left-title" class="signature-title">{{ $managerTitle }}</div>

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
    <script src="{{ url('js/students/certificate-save.js') }}?v={{ file_exists(public_path('js/students/certificate-save.js')) ? filemtime(public_path('js/students/certificate-save.js')) : time() }}"></script>
@endsection
