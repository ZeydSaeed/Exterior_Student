(function () {
    'use strict';

    function pad2(n) {
        n = String(n || '');
        return n.length >= 2 ? n : ('0' + n).slice(-2);
    }

    function toWestern(value) {
        if (window.ArabicDate && typeof window.ArabicDate.toWesternDigits === 'function') {
            return window.ArabicDate.toWesternDigits(String(value || ''));
        }
        return String(value || '')
            .replace(/[٠-٩]/g, function (d) {
                return String('٠١٢٣٤٥٦٧٨٩'.indexOf(d));
            })
            .replace(/[۰-۹]/g, function (d) {
                return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
            });
    }

    function syncArabicDates(rowRoot) {
        rowRoot.querySelectorAll('.arabic-date-field-wrap').forEach(function (wrap) {
            var dateInput = wrap.querySelector('input[type="date"].arabic-date-field, input[type="date"][name="document_date"]');
            var day = wrap.querySelector('.arabic-date-day');
            var month = wrap.querySelector('.arabic-date-month');
            var year = wrap.querySelector('.arabic-date-year');
            if (!dateInput) {
                return;
            }
            if (!day || !month || !year) {
                return;
            }
            var d = toWestern(day.value).replace(/\D/g, '');
            var m = toWestern(month.value).replace(/\D/g, '');
            var y = toWestern(year.value).replace(/\D/g, '');
            if (!d && !m && !y) {
                dateInput.value = '';
                return;
            }
            if (d && m && y && y.length === 4) {
                var di = parseInt(d, 10);
                var mi = parseInt(m, 10);
                var yi = parseInt(y, 10);
                if (di >= 1 && di <= 31 && mi >= 1 && mi <= 12) {
                    dateInput.value = yi + '-' + pad2(mi) + '-' + pad2(di);
                }
            }
        });
    }

    function setRowEditing(rowRoot, editing) {
        if (!rowRoot) {
            return;
        }
        rowRoot.classList.toggle('documents-row-editing', editing);

        rowRoot.querySelectorAll('.documents-row-field').forEach(function (el) {
            if (editing) {
                el.removeAttribute('readonly');
            } else {
                el.setAttribute('readonly', 'readonly');
            }
        });

        rowRoot.querySelectorAll('.arabic-date-part').forEach(function (el) {
            if (editing) {
                el.removeAttribute('readonly');
                el.removeAttribute('disabled');
            } else {
                el.setAttribute('readonly', 'readonly');
            }
        });

        if (editing) {
            initNotesAutoGrow(rowRoot);
        } else {
            rowRoot.querySelectorAll('textarea.doc-field-notes').forEach(resizeNotesField);
        }
    }

    function ensureHidden(form, name) {
        var existing = form.querySelector('input[data-documents-sync="' + name + '"]');
        if (existing) {
            return existing;
        }
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.setAttribute('data-documents-sync', name);
        form.appendChild(input);
        return input;
    }

    function submitRow(rowRoot) {
        var form = rowRoot.querySelector('form.documents-row-form');
        if (!form) {
            return;
        }

        setRowEditing(rowRoot, true);
        syncArabicDates(rowRoot);

        var fields = {
            document_number: rowRoot.querySelector('input.documents-row-field[name="document_number"]'),
            document_date: rowRoot.querySelector('input.documents-row-field[name="document_date"], input[type="date"][name="document_date"]'),
            addressee: rowRoot.querySelector('.documents-row-field[name="addressee"]'),
            purpose: rowRoot.querySelector('input.documents-row-field[name="purpose"]'),
            notes: rowRoot.querySelector('textarea.documents-row-field[name="notes"]')
        };

        Object.keys(fields).forEach(function (name) {
            var el = fields[name];
            var hidden = ensureHidden(form, name);
            hidden.value = el ? String(el.value || '') : '';
        });

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    function resizeNotesField(ta) {
        if (!ta) {
            return;
        }
        ta.style.height = 'auto';
        ta.style.height = Math.max(ta.scrollHeight, 32) + 'px';
    }

    function initNotesAutoGrow(root) {
        var scope = root || document;
        scope.querySelectorAll('textarea.doc-field-notes').forEach(function (ta) {
            if (ta.getAttribute('data-notes-autogrow') === '1') {
                resizeNotesField(ta);
                return;
            }
            ta.setAttribute('data-notes-autogrow', '1');
            ta.setAttribute('rows', '1');
            resizeNotesField(ta);
            ta.addEventListener('input', function () {
                resizeNotesField(ta);
            });
        });
    }

    function initRow(rowRoot) {
        if (!rowRoot || rowRoot.getAttribute('data-documents-row-ready') === '1') {
            return;
        }
        rowRoot.setAttribute('data-documents-row-ready', '1');
        setRowEditing(rowRoot, false);
        initNotesAutoGrow(rowRoot);

        var btnEdit = rowRoot.querySelector('[data-documents-edit]');
        if (btnEdit) {
            btnEdit.addEventListener('click', function () {
                setRowEditing(rowRoot, true);
                initNotesAutoGrow(rowRoot);
                var first = rowRoot.querySelector('.documents-row-field');
                if (first && typeof first.focus === 'function') {
                    first.focus();
                }
            });
        }

        var btnSave = rowRoot.querySelector('[data-documents-save]');
        if (btnSave) {
            btnSave.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                submitRow(rowRoot);
            });
        }
    }

    function initAll() {
        document.querySelectorAll('[data-documents-row]').forEach(initRow);
        initNotesAutoGrow(document);
        setTimeout(function () {
            document.querySelectorAll('[data-documents-row]:not(.documents-row-editing)').forEach(function (rowRoot) {
                setRowEditing(rowRoot, false);
            });
            initNotesAutoGrow(document);
        }, 0);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
