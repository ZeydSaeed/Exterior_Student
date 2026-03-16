<div id="grades-modal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="grades-modal-title">
    <div class="modal grades-modal" role="document">
        <div class="modal-header">
            <h2 id="grades-modal-title">عرض الدرجات</h2>
            <div class="grades-modal-toolbar">
                <button type="button" class="btn-primary grades-btn-edit" id="grades-btn-edit">تعديل</button>
                <button type="button" class="btn-primary grades-btn-save" id="grades-btn-save" style="display: none;">حفظ</button>
                <button type="button" class="btn-primary grades-btn-cancel" id="grades-btn-cancel" style="display: none;">إلغاء</button>
                <button type="button" class="modal-close" data-grades-modal-close aria-label="إغلاق">&times;</button>
            </div>
        </div>
        <form id="grades-form" class="grades-form">
            <input type="hidden" name="student_id" id="grades-student-id">
            <div class="grades-form-grid">
                <div class="grades-field">
                    <label>اسم الطالب</label>
                    <span class="grades-readonly" id="grades-name-student"></span>
                    <input type="text" name="name_student" id="grades-name-student-input" class="grades-edit grades-edit-hidden" aria-label="اسم الطالب">
                </div>
                <div class="grades-field">
                    <label>اسم الأب</label>
                    <span class="grades-readonly" id="grades-name-father"></span>
                    <input type="text" name="name_father" id="grades-name-father-input" class="grades-edit grades-edit-hidden" aria-label="اسم الأب">
                </div>
                <div class="grades-field">
                    <label>اسم الجد</label>
                    <span class="grades-readonly" id="grades-name-grandfather"></span>
                    <input type="text" name="name_grandfather" id="grades-name-grandfather-input" class="grades-edit grades-edit-hidden" aria-label="اسم الجد">
                </div>
                <div class="grades-field">
                    <label>اللقب</label>
                    <span class="grades-readonly" id="grades-name-surname"></span>
                    <input type="text" name="name_surname" id="grades-name-surname-input" class="grades-edit grades-edit-hidden" aria-label="اللقب">
                </div>
                <div class="grades-field grades-field-exam-number">
                    <label for="grades-exam-number-input">الرقم الامتحاني <span class="grades-required" aria-hidden="true">*</span></label>
                    <span class="grades-readonly" id="grades-exam-number"></span>
                    <input type="text" name="exam_number" id="grades-exam-number-input" class="grades-edit grades-edit-hidden" aria-label="الرقم الامتحاني (مطلوب)" required maxlength="255">
                </div>
                <div class="grades-field">
                    <label>الجنس</label>
                    <span class="grades-readonly" id="grades-gender"></span>
                    <select name="gender" id="grades-gender-input" class="grades-edit grades-edit-hidden grades-select" aria-label="الجنس">
                        <!-- <option value="">— اختر —</option> -->
                        @foreach($genders ?? [] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grades-field">
                    <label>الفرع</label>
                    <span class="grades-readonly" id="grades-branch"></span>
                    <input type="text" name="branch" id="grades-branch-input" class="grades-edit grades-edit-hidden" aria-label="الفرع">
                </div>
                <div class="grades-field">
                    <label>الاختصاص</label>
                    <span class="grades-readonly" id="grades-major"></span>
                    <input type="text" name="major" id="grades-major-input" class="grades-edit grades-edit-hidden" aria-label="الاختصاص">
                </div>
                <div class="grades-field">
                    <label>العام الدراسي</label>
                    <span class="grades-readonly" id="grades-year"></span>
                    <input type="text" name="academic_year" id="grades-year-input" class="grades-edit grades-edit-hidden" aria-label="العام الدراسي">
                </div>
            </div>

            <div class="grades-table-wrap">
                <h3 class="grades-table-title">الدرجات</h3>
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th>المادة</th>
                            <th>الدرجة</th>
                        </tr>
                    </thead>
                    <tbody id="grades-tbody">
                    </tbody>
                </table>
            </div>

            <div class="grades-summary">
                <div class="grades-field">
                    <label>المجموع</label>
                    <span class="grades-readonly" id="grades-total"></span>
                    <input type="text" name="total" id="grades-total-input" class="grades-edit grades-edit-hidden" aria-label="المجموع">
                </div>
                <div class="grades-field">
                    <label>المعدل</label>
                    <span class="grades-readonly" id="grades-average"></span>
                    <input type="text" name="average" id="grades-average-input" class="grades-edit grades-edit-hidden" aria-label="المعدل">
                </div>
                <div class="grades-field">
                    <label>النتيجة</label>
                    <span class="grades-readonly" id="grades-result"></span>
                    <select name="result" id="grades-result-input" class="grades-edit grades-edit-hidden grades-select" aria-label="النتيجة">
                        <!-- <option value="">— اختر —</option> -->
                        @foreach($resultOptions ?? [] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grades-field">
                    <label>الدور</label>
                    <span class="grades-readonly" id="grades-round"></span>
                    <select name="round" id="grades-round-input" class="grades-edit grades-edit-hidden grades-select" aria-label="الدور">
                        <!-- <option value="">— اختر —</option> -->
                        @foreach($roundOptions ?? [] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
