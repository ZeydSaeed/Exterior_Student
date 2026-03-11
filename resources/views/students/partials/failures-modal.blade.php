<div id="failures-modal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="failures-modal-title">
    <div class="modal failures-modal" role="document">
        <div class="failures-modal-title-block">
            <h2 id="failures-modal-title">حذف الطلبة الراسبين</h2>
            <button type="button" class="modal-close" data-failures-modal-close aria-label="إغلاق">&times;</button>
        </div>

        <p class="failures-modal-warning">
            سيتم حذف جميع الطلبة الذين نتيجتهم <strong>راسب</strong> أو <strong>معيد</strong> وفق الفلاتر التالية:
        </p>

        <div class="failures-modal-filters-row">
            <span>الفرع: <strong id="failures-filter-branch">الكل</strong></span>
        </div>
        <div class="failures-modal-filters-row">
            <span>الاختصاص: <strong id="failures-filter-major">الكل</strong></span>
        </div>
        <div class="failures-modal-filters-row">
            <span>الجنس: <strong id="failures-filter-gender">الكل</strong></span>
        </div>
        <div class="failures-modal-filters-row">
            <span>العام الدراسي: <strong id="failures-filter-year">الكل</strong></span>
        </div>

        <p class="failures-modal-confirm-text">
            هل أنت متأكد من تنفيذ عملية الحذف؟
        </p>

        <div class="failures-modal-actions">
            <button type="button" class="btn-primary" id="failures-btn-confirm">موافق</button>
            <button type="button" class="btn-primary" data-failures-modal-close>إلغاء</button>
        </div>
    </div>
</div>
