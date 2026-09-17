(function () {
    var form = document.getElementById('database-restore-form');
    var input = document.getElementById('database-restore-file');
    var trigger = document.getElementById('database-restore-trigger');

    if (!form || !input || !trigger) {
        return;
    }

    trigger.addEventListener('click', function () {
        input.click();
    });

    input.addEventListener('change', function () {
        if (!input.files || input.files.length === 0) {
            return;
        }

        var confirmed = window.confirm(
            'سيتم استبدال بيانات قاعدة البيانات الحالية بمحتوى الملف المختار. هل تريد المتابعة؟'
        );

        if (!confirmed) {
            input.value = '';
            return;
        }

        form.submit();
    });
})();
