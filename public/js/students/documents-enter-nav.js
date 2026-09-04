(function () {
    'use strict';

    var addFieldIds = [
        'document-add-number',
        'document-add-date',
        'document-add-addressee',
        'document-add-purpose',
        'document-add-submit'
    ];

    function focusControl(el) {
        if (!el) {
            return false;
        }
        if (el.type === 'date' || (el.classList && el.classList.contains('arabic-date-field'))) {
            var wrap = el.closest ? el.closest('.arabic-date-field-wrap') : null;
            var day = wrap ? wrap.querySelector('.arabic-date-day') : null;
            if (day) {
                day.focus();
                if (typeof day.select === 'function') {
                    try {
                        day.select();
                    } catch (err) {
                        // ignore
                    }
                }
                return true;
            }
        }
        el.focus();
        if (typeof el.select === 'function' && el.tagName === 'INPUT' && el.type !== 'date' && el.type !== 'submit') {
            try {
                el.select();
            } catch (err2) {
                // ignore
            }
        }
        return true;
    }

    function focusNextButton() {
        var nextBtn = document.querySelector('.documents-next-btn');
        if (!nextBtn) {
            return false;
        }
        nextBtn.focus();
        return true;
    }

    function resolveAddFieldId(el) {
        if (!el) {
            return '';
        }
        if (el.id && addFieldIds.indexOf(el.id) !== -1) {
            return el.id;
        }
        if (el.classList && el.classList.contains('arabic-date-part')) {
            var wrap = el.closest('.arabic-date-field-wrap');
            var dateInput = wrap ? wrap.querySelector('input[type="date"]') : null;
            if (dateInput && dateInput.id === 'document-add-date') {
                return 'document-add-date';
            }
            if (dateInput && dateInput.classList && dateInput.classList.contains('documents-row-field')) {
                return 'row-date';
            }
        }
        return el.id || '';
    }

    function focusNextAddField(fieldId) {
        var idx = addFieldIds.indexOf(fieldId);
        if (idx === -1) {
            return;
        }
        for (var i = idx + 1; i < addFieldIds.length; i++) {
            var next = document.getElementById(addFieldIds[i]);
            if (next && focusControl(next)) {
                return;
            }
        }
        focusNextButton();
    }

    function focusNextDatePart(part) {
        var wrap = part.closest('.arabic-date-field-wrap');
        if (!wrap) {
            return false;
        }
        var parts = [
            wrap.querySelector('.arabic-date-day'),
            wrap.querySelector('.arabic-date-month'),
            wrap.querySelector('.arabic-date-year')
        ].filter(Boolean);
        var i = parts.indexOf(part);
        if (i !== -1 && i < parts.length - 1) {
            focusControl(parts[i + 1]);
            return true;
        }
        return false;
    }

    function editableRowFields(rowRoot) {
        return Array.prototype.slice.call(rowRoot.querySelectorAll('.documents-row-field')).filter(function (el) {
            return !el.readOnly && !el.disabled;
        });
    }

    function initAddFormEnter() {
        var form = document.querySelector('.document-add-form');
        if (!form) {
            return;
        }

        form.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') {
                return;
            }
            var target = e.target;
            if (!target) {
                return;
            }

            if (target.classList && target.classList.contains('arabic-date-part')) {
                e.preventDefault();
                if (focusNextDatePart(target)) {
                    return;
                }
                focusNextAddField('document-add-date');
                return;
            }

            var fieldId = resolveAddFieldId(target);
            if (fieldId === 'document-add-submit') {
                e.preventDefault();
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(target);
                } else {
                    target.click();
                }
                return;
            }

            if (addFieldIds.indexOf(fieldId) !== -1) {
                e.preventDefault();
                focusNextAddField(fieldId);
            }
        });
    }

    function initNextButtonEnter() {
        var nextBtn = document.querySelector('.documents-next-btn');
        if (!nextBtn) {
            return;
        }
        nextBtn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                nextBtn.click();
            }
        });
    }

    function initRowEnter(rowRoot) {
        rowRoot.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') {
                return;
            }
            if (!rowRoot.classList.contains('documents-row-editing')) {
                return;
            }
            var target = e.target;
            if (!target) {
                return;
            }
            if (target.closest && target.closest('[data-documents-save], [data-documents-edit], .btn-delete-row')) {
                return;
            }

            if (target.classList && target.classList.contains('arabic-date-part')) {
                e.preventDefault();
                if (focusNextDatePart(target)) {
                    return;
                }
                var fields = editableRowFields(rowRoot);
                var dateInput = rowRoot.querySelector('[name="document_date"]');
                var di = fields.indexOf(dateInput);
                if (di !== -1 && di < fields.length - 1) {
                    focusControl(fields[di + 1]);
                    return;
                }
                var saveAfterDate = rowRoot.querySelector('[data-documents-save]');
                if (saveAfterDate) {
                    saveAfterDate.focus();
                    return;
                }
                focusNextButton();
                return;
            }

            if (!(target.classList && target.classList.contains('documents-row-field'))) {
                return;
            }

            e.preventDefault();
            var rowFields = editableRowFields(rowRoot);
            var idx = rowFields.indexOf(target);
            if (idx !== -1 && idx < rowFields.length - 1) {
                focusControl(rowFields[idx + 1]);
                return;
            }
            var saveBtn = rowRoot.querySelector('[data-documents-save]');
            if (saveBtn) {
                saveBtn.focus();
                return;
            }
            focusNextButton();
        });

        var saveBtn = rowRoot.querySelector('[data-documents-save]');
        if (saveBtn && saveBtn.getAttribute('data-enter-next-bound') !== '1') {
            saveBtn.setAttribute('data-enter-next-bound', '1');
            saveBtn.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter') {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                saveBtn.click();
            });
        }
    }

    function initAllRowsEnter() {
        document.querySelectorAll('[data-documents-row]').forEach(function (rowRoot) {
            if (rowRoot.getAttribute('data-documents-enter-ready') === '1') {
                return;
            }
            rowRoot.setAttribute('data-documents-enter-ready', '1');
            initRowEnter(rowRoot);
        });
    }

    function init() {
        initAddFormEnter();
        initNextButtonEnter();
        initAllRowsEnter();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
