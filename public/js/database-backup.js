(function () {
    var form = document.getElementById('database-backup-form');
    var trigger = document.getElementById('database-backup-trigger');

    if (!form || !trigger) {
        return;
    }

    function notifyError(message) {
        if (window.AppErrorDialog && typeof window.AppErrorDialog.error === 'function') {
            window.AppErrorDialog.error(message);
            return;
        }

        window.alert(message);
    }

    function notifySuccess(message) {
        if (window.AppErrorDialog && typeof window.AppErrorDialog.info === 'function') {
            window.AppErrorDialog.info(message);
        }
    }

    trigger.addEventListener('click', function () {
        trigger.disabled = true;

        var headers = {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        };
        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf && csrf.getAttribute('content')) {
            headers['X-CSRF-TOKEN'] = csrf.getAttribute('content');
        }

        var body = new FormData(form);
        body.append('pick_path', '1');

        fetch(form.action, {
            method: 'POST',
            headers: headers,
            body: body,
            credentials: 'same-origin',
        }).then(function (response) {
            return response.json().catch(function () {
                return { message: 'تعذر إنشاء النسخ الاحتياطي.' };
            }).then(function (payload) {
                if (payload && payload.cancelled) {
                    return;
                }

                if (!response.ok) {
                    throw new Error((payload && payload.message) || 'تعذر إنشاء النسخ الاحتياطي.');
                }

                notifySuccess((payload && payload.message) || 'تم حفظ النسخ الاحتياطي في الموقع الذي اخترته.');
            });
        }).catch(function (error) {
            if (error instanceof Response) {
                return;
            }

            notifyError((error && error.message) || 'تعذر إنشاء النسخ الاحتياطي.');
        }).finally(function () {
            trigger.disabled = false;
        });
    });
})();
