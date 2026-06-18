(function () {
    'use strict';

    var TITLES = {
        error: 'خطأ',
        warning: 'تحذير',
        info: 'تنبيه',
    };

    var GENERIC = {
        error: 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.',
        warning: 'يرجى مراجعة البيانات والمحاولة مرة أخرى.',
        info: 'يرجى الانتباه إلى الملاحظة التالية.',
        fetch: 'تعذر إتمام العملية. يرجى المحاولة مرة أخرى.',
        page: 'حدث خطأ في الصفحة. حدّث الصفحة ثم أعد المحاولة.',
    };

    function getDialog() {
        return document.getElementById('app-error-dialog');
    }

    function normalizeType(type) {
        if (type === 'warning' || type === 'info') {
            return type;
        }
        return 'error';
    }

    function isTechnicalMessage(text) {
        if (!text || typeof text !== 'string') {
            return true;
        }
        var patterns = [
            /SQLSTATE\[/i,
            /Illuminate\\/,
            /Symfony\\/,
            /PDOException/i,
            /QueryException/i,
            /Stack trace/i,
            /vendor\\/,
            /\.php:\d+/,
            /::\w+\(/,
            /Undefined (array key|variable|index)/i,
            /Call to (a )?member function/i,
        ];
        return patterns.some(function (pattern) {
            return pattern.test(text);
        });
    }

    function sanitizeMessage(message, type) {
        var text = (message || '').toString().trim();
        if (text === '' || isTechnicalMessage(text)) {
            return GENERIC[type] || GENERIC.error;
        }
        return text;
    }

    function applyDialogType(dialog, type) {
        dialog.classList.remove('app-error-dialog--error', 'app-error-dialog--warning', 'app-error-dialog--info');
        dialog.classList.add('app-error-dialog--' + type);
        dialog.setAttribute('data-dialog-type', type);
    }

    function show(input) {
        var dialog = getDialog();
        var options = typeof input === 'string' ? { message: input } : (input || {});
        var type = normalizeType(options.type);
        var title = options.title || TITLES[type] || TITLES.error;
        var message = sanitizeMessage(options.message, type);

        if (!dialog) {
            if (message) {
                window.alert(title + '\n\n' + message);
            }
            return;
        }

        var titleEl = dialog.querySelector('[data-app-error-title]');
        var textEl = dialog.querySelector('[data-app-error-text]');
        if (titleEl) {
            titleEl.textContent = title;
        }
        if (textEl) {
            textEl.textContent = message;
        }

        applyDialogType(dialog, type);

        dialog.removeAttribute('hidden');
        dialog.classList.add('is-visible');
        dialog.setAttribute('aria-hidden', 'false');
    }

    function hide() {
        var dialog = getDialog();
        if (!dialog) {
            return;
        }

        dialog.classList.remove('is-visible');
        dialog.setAttribute('aria-hidden', 'true');
        dialog.setAttribute('hidden', 'hidden');
    }

    function bindCloseHandlers() {
        var dialog = getDialog();
        if (!dialog || dialog.dataset.bound === '1') {
            return;
        }
        dialog.dataset.bound = '1';
        dialog.querySelectorAll('[data-app-error-close]').forEach(function (btn) {
            btn.addEventListener('click', hide);
        });
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                hide();
            }
        });
    }

    function extractPayload(data) {
        if (!data || typeof data !== 'object') {
            return null;
        }
        var message = typeof data.message === 'string' ? data.message.trim() : '';
        if (message === '' && typeof data.error === 'string') {
            message = data.error.trim();
        }
        if (message === '') {
            return null;
        }
        return {
            type: normalizeType(data.type),
            title: data.title || TITLES[normalizeType(data.type)],
            message: message,
        };
    }

    function installFetchDialog() {
        if (!window.fetch || window.fetch.__appErrorDialogPatched) {
            return;
        }
        var originalFetch = window.fetch.bind(window);
        window.fetch = function () {
            return originalFetch.apply(window, arguments).then(function (response) {
                if (response.ok) {
                    return response;
                }
                var clone = response.clone();
                return clone.json()
                    .then(function (data) {
                        var payload = extractPayload(data);
                        show({
                            type: (payload && payload.type) || 'error',
                            title: (payload && payload.title) || TITLES.error,
                            message: (payload && payload.message) || GENERIC.fetch,
                        });
                        return Promise.reject(response);
                    })
                    .catch(function () {
                        show({ type: 'error', message: GENERIC.fetch });
                        return Promise.reject(response);
                    });
            });
        };
        window.fetch.__appErrorDialogPatched = true;
    }

    window.AppErrorDialog = {
        show: show,
        hide: hide,
        error: function (message, options) {
            show(Object.assign({}, options || {}, { type: 'error', message: message }));
        },
        warning: function (message, options) {
            show(Object.assign({}, options || {}, { type: 'warning', message: message }));
        },
        info: function (message, options) {
            show(Object.assign({}, options || {}, { type: 'info', message: message }));
        },
    };

    document.addEventListener('DOMContentLoaded', function () {
        bindCloseHandlers();
        installFetchDialog();
    });

    window.addEventListener('error', function () {
        show({ type: 'error', message: GENERIC.page });
    });

    window.addEventListener('unhandledrejection', function () {
        show({ type: 'error', message: GENERIC.fetch });
    });
})();
