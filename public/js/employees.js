(function () {
    function getSignaturesUrl() {
        var el = document.querySelector('.page-employees [data-signatures-url]');
        if (el) {
            var url = el.getAttribute('data-signatures-url');
            if (url) return url;
        }
        var meta = document.querySelector('meta[name="employees-signatures-url"]');
        return meta ? (meta.getAttribute('content') || '') : '';
    }

    function getSelectedIds() {
        var right = document.querySelector('input[name="right_signature"]:checked');
        var left = document.querySelector('input[name="left_signature"]:checked');
        return [
            right && right.value ? right.value : '',
            left && left.value ? left.value : ''
        ];
    }

    function saveSignaturesAjax() {
        var url = getSignaturesUrl();
        if (!url) return;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var token = csrf ? csrf.getAttribute('content') : '';
        var ids = getSelectedIds();
        var body = new FormData();
        body.append('_token', token);
        body.append('right_signature', ids[0] || '');
        body.append('left_signature', ids[1] || '');
        fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: body
        }).catch(function () {});
    }

    function init() {
        var container = document.querySelector('.page-employees');
        if (!container) return;

        var url = getSignaturesUrl();
        if (!url) return;

        var radiosRight = container.querySelectorAll('input[name="right_signature"]');
        var radiosLeft = container.querySelectorAll('input[name="left_signature"]');
        radiosRight.forEach(function (r) { r.addEventListener('change', saveSignaturesAjax); });
        radiosLeft.forEach(function (r) { r.addEventListener('change', saveSignaturesAjax); });

        container.querySelectorAll('form[method="POST"]').forEach(function (form) {
            var formAction = form.getAttribute('action') || form.action || '';
            if (formAction.indexOf('signatures') !== -1) return;
            form.addEventListener('submit', function () {
                var ids = getSelectedIds();
                if (ids[0]) {
                    if (!form.querySelector('input[name="right_signature"]')) {
                        var r = document.createElement('input');
                        r.type = 'hidden';
                        r.name = 'right_signature';
                        r.value = ids[0];
                        form.appendChild(r);
                    }
                }
                if (ids[1]) {
                    if (!form.querySelector('input[name="left_signature"]')) {
                        var l = document.createElement('input');
                        l.type = 'hidden';
                        l.name = 'left_signature';
                        l.value = ids[1];
                        form.appendChild(l);
                    }
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
