<div id="certificate-modal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-label="تأييد">
    <div class="modal certificate-modal" role="document">
        <div class="modal-actions modal-actions-top no-print">
            <button type="button" class="btn print" id="certificate-btn-print">طباعة</button>
            <button type="button" class="btn close" data-certificate-modal-close>إغلاق</button>
        </div>
        <div class="modal-body">
            <div class="support-paper support-paper-with-grades print-area" contenteditable="true">
                <div class="top-line">جمهورية العراق</div>
                <div class="right-line">وزارة التربية</div>
                <div class="right-line2">قسم التعليم المهني / كربلاء المقدسة</div>
                <div class="right-line2">المدرسة: خارجيون</div>

                <div class="photo-frame">صورة الطالب</div>

                <div class="meta-line">العدد: <span class="editable arabic-number" contenteditable="true" id="certificate-number"></span></div>
                <div class="meta-line">التاريخ: <span class="editable arabic-date" contenteditable="true" id="certificate-date"></span></div>
                <div class="meta-line">الرقم الامتحاني: <span class="arabic-number" id="certificate-exam-number"></span></div>
                <br>
                <br>

                <div class="meta-line to-line to-flex-line">
                    <strong>الى /</strong> <span class="editable" contenteditable="true" id="certificate-to"></span>
                </div>
                <div class="topic-line subject-line">
                    <strong>الموضوع / تأييد</strong>
                </div>
                <div class="body-line support-body-text" id="certificate-body">
                    نؤيد لكم أن الطالب (<span id="certificate-full-name"></span>) الملصقة صورته أعلاه،
                    والمولود بتاريخ
                    <span class="nowrap arabic-date" id="certificate-birth-date"></span>
                    أحد طلاب الصف الثالث إعدادي مهني،
                    الفرع (<span id="certificate-branch"></span>) /
                    الاختصاص <span class="nowrap" id="certificate-specialization"></span>،
                    اشترك بالامتحانات الوزارية للعام الدراسي
                    <span class="nowrap arabic-number" id="certificate-academic-year"></span>
                    وكانت النتيجة (<span id="certificate-result"></span>)
                    في الدور (<span id="certificate-round"></span>)،
                    وبناءً على طلبه زُوِّد بهذا التأييد.
                </div>

                <div style="height:200px;"></div>

                <div class="signature-columns">
                    <div class="signature-col">
                        <div class="signature-title" id="certificate-organizer-title"></div>
                        <div class="signature-name" id="certificate-organizer-name"></div>
                        <div class="signature-date arabic-date" id="certificate-organizer-date"></div>
                    </div>
                    <div class="signature-col">
                        <div class="signature-title" id="certificate-manager-title"></div>
                        <div class="signature-name" id="certificate-manager-name"></div>
                        <div class="signature-date arabic-date" id="certificate-manager-date"></div>
                    </div>
                </div>

                <div class="footer-note">* التأييد خالٍ من الحك والشطب والتحريف.</div>
            </div>
        </div>
    </div>
</div>
