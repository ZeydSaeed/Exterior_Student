@extends('layouts.dashboard')

@section('title', 'إضافة طالب')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-employees page-add-student')

@section('content')
    <div class="employees-page-wrap">
        {{-- أزرار الحفظ والإغلاق: حفظ أولاً فيصل لليمين في RTL، ثم إغلاق — حفظ بجانب يمين إغلاق --}}
        <div class="employees-page-actions-top">
            <button type="submit" form="form-add-student" class="btn-primary btn-save-top">حفظ</button>
            <a href="{{ route('students.index') }}" class="btn-primary btn-close-top" title="إغلاق">إغلاق</a>
        </div>

        <div class="employees-page-header">
            <h1>إضافة طالب</h1>
        </div>

        @if($errors->any())
            <ul class="employees-error-list">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('students.store') }}" id="form-add-student" class="form-add-student">
            @csrf
            {{-- السطر الأول: الرقم الامتحاني --}}
            <div class="form-row form-row-1">
                <div class="form-group">
                    <label for="exam_number">الرقم الامتحاني <span class="required">*</span></label>
                    <input type="text" id="exam_number" name="exam_number" value="{{ old('exam_number') }}" required maxlength="255" />
                </div>
            </div>
            {{-- السطر الثاني: اسم الطالب، اسم الاب، اسم الجد، اللقب --}}
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="name_student">اسم الطالب <span class="required">*</span></label>
                    <input type="text" id="name_student" name="name_student" value="{{ old('name_student') }}" required maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="name_father">اسم الاب <span class="required">*</span></label>
                    <input type="text" id="name_father" name="name_father" value="{{ old('name_father') }}" required maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="name_grandfather">اسم الجد</label>
                    <input type="text" id="name_grandfather" name="name_grandfather" value="{{ old('name_grandfather') }}" maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="name_surname">اللقب <span class="required">*</span></label>
                    <input type="text" id="name_surname" name="name_surname" value="{{ old('name_surname') }}" required maxlength="255" />
                </div>
            </div>
            {{-- السطر الثالث: محل الولادة، التولد، اسم الام الكامل، الجنس --}}
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="birth_place">محل الولادة</label>
                    <input type="text" id="birth_place" name="birth_place" value="{{ old('birth_place') }}" maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="birth_date">التولد</label>
                    <input type="text" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" placeholder="مثال: 2008-05-15" maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="mother_full_name">اسم الام الكامل</label>
                    <input type="text" id="mother_full_name" name="mother_full_name" value="{{ old('mother_full_name') }}" maxlength="255" />
                </div>
                <div class="form-group form-group-radio">
                    <span class="label">الجنس <span class="required">*</span></span>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="ذكر" {{ old('gender') === 'ذكر' ? 'checked' : '' }} /> ذكر</label>
                        <label><input type="radio" name="gender" value="أنثى" {{ old('gender', '') === 'أنثى' || old('gender') === 'انثى' ? 'checked' : '' }} /> أنثى</label>
                    </div>
                </div>
            </div>
            {{-- السطر الرابع: الفرع، الاختصاص، العام الدراسي --}}
            <div class="form-row form-row-4">
                <div class="form-group form-group-radio">
                    <span class="label">الفرع <span class="required">*</span></span>
                    <div class="radio-group radio-group-branch" id="branch-radios">
                        <label><input type="radio" name="branch" value="الصناعي" {{ old('branch') === 'الصناعي' ? 'checked' : '' }} /> الصناعي</label>
                        <label><input type="radio" name="branch" value="الزراعي" {{ old('branch') === 'الزراعي' ? 'checked' : '' }} /> الزراعي</label>
                        <label><input type="radio" name="branch" value="الحاسوب وتقنية المعلومات" {{ old('branch') === 'الحاسوب وتقنية المعلومات' ? 'checked' : '' }} /> الحاسوب وتقنية المعلومات</label>
                        <label><input type="radio" name="branch" value="فنون تطبيقية" {{ old('branch') === 'فنون تطبيقية' ? 'checked' : '' }} /> فنون تطبيقية</label>
                        <label><input type="radio" name="branch" value="التجاري" {{ old('branch') === 'التجاري' ? 'checked' : '' }} /> التجاري</label>
                        <label><input type="radio" name="branch" value="السياحة والفندقة" {{ old('branch') === 'السياحة والفندقة' ? 'checked' : '' }} /> السياحة والفندقة</label>
                    </div>
                </div>
                <div class="form-group form-group-radio">
                    <span class="label">الاختصاص <span class="required">*</span></span>
                    <div class="radio-group radio-group-major" id="major-radios" data-old="{{ old('major', '') }}">
                        {{-- يُملأ بالجافاسكربت حسب الفرع --}}
                    </div>
                </div>
                <div class="form-group form-group-radio">
                    <span class="label">العام الدراسي <span class="required">*</span></span>
                    <div class="radio-group radio-group-years">
                        @foreach($academicYears as $index => $year)
                            <label><input type="radio" name="academic_year" value="{{ $year }}" {{ old('academic_year') === $year ? 'checked' : (!old('academic_year') && $index === 0 ? 'checked' : '') }} /> {{ $year }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- السطر الخامس: اخر مدرسة، رقم الوثيقة المتوسطة، تاريخها، جهة الاصدار --}}
            <div class="form-row form-row-5">
                <div class="form-group">
                    <label for="last_school">اخر مدرسة كان فيها الطالب</label>
                    <input type="text" id="last_school" name="last_school" value="{{ old('last_school') }}" maxlength="500" />
                </div>
                <div class="form-group">
                    <label for="middle_doc_number">رقم الوثيقة المتوسطة</label>
                    <input type="text" id="middle_doc_number" name="middle_doc_number" value="{{ old('middle_doc_number') }}" maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="middle_doc_date">تاريخها</label>
                    <input type="text" id="middle_doc_date" name="middle_doc_date" value="{{ old('middle_doc_date') }}" placeholder="مثال: 2023-07-01" maxlength="255" />
                </div>
                <div class="form-group">
                    <label for="issuing_authority">جهة الاصدار</label>
                    <input type="text" id="issuing_authority" name="issuing_authority" value="{{ old('issuing_authority') }}" maxlength="500" />
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    var subjectObject = {
        "الصناعي": { "كهرباء": [], "ميكانيك": [], "سيارات": [], "اللحام وتشكيل المعادن": [], "الكترونيك وسيطرة": [], "البناء": [], "تكييف الهواء والتثليج": [], "الصناعات البتروكيماوية": [] },
        "الزراعي": { "زراعي": [] },
        "الحاسوب وتقنية المعلومات": { "تجميع وصيانة الحاسوب": [], "شبكات الحاسوب": [], "الامن السبراني": [] },
        "فنون تطبيقية": { "فن التربية الاسرية": [] },
        "التجاري": { "محاسبة": [], "ادارة": [] },
        "السياحة والفندقة": { "الادارة السياحية": [], "الاسكان الفندقي": [], "الضيافة وانتاج الاطعمة": [] }
    };

    var branchRadios = document.querySelectorAll('input[name="branch"]');
    var majorContainer = document.getElementById('major-radios');
    var oldMajor = (majorContainer && majorContainer.getAttribute('data-old')) || '';

    function getSelectedBranch() {
        var i;
        for (i = 0; i < branchRadios.length; i++) {
            if (branchRadios[i].checked) return branchRadios[i].value;
        }
        return '';
    }

    function fillMajorRadios() {
        var branch = getSelectedBranch();
        if (!majorContainer) return;
        majorContainer.innerHTML = '';
        if (!branch || !subjectObject[branch]) return;
        var majors = subjectObject[branch];
        for (var key in majors) {
            if (majors.hasOwnProperty(key)) {
                var label = document.createElement('label');
                var radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'major';
                radio.value = key;
                if (key === oldMajor) radio.checked = true;
                label.appendChild(radio);
                label.appendChild(document.createTextNode(' ' + key));
                majorContainer.appendChild(label);
            }
        }
    }

    branchRadios.forEach(function (r) { r.addEventListener('change', fillMajorRadios); });
    fillMajorRadios();
})();
</script>
@endsection
