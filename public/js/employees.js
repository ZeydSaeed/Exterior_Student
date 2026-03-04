(function () {
    const STORAGE_KEY = 'selected_employees';
    const MAX_SELECTION = 2;
    const sessionUrl = document.querySelector('meta[name="session-url"]') && document.querySelector('meta[name="session-url"]').getAttribute('content');
    const csrfToken = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function sendSelectedToSession(ids) {
        if (!sessionUrl || !csrfToken || !ids.length) return;
        fetch(sessionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: ids })
        }).catch(function () {});
    }

    function initEmployeeSelectionLimit() {
        const checkboxes = document.querySelectorAll('.employee-select');
        if (!checkboxes.length) return;

        let saved;
        try {
            saved = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
            if (!Array.isArray(saved)) saved = [];
        } catch (e) {
            saved = [];
        }

        checkboxes.forEach(function (cb) {
            if (saved.indexOf(cb.value) !== -1) cb.checked = true;
        });
        if (saved.length) sendSelectedToSession(saved.map(function (v) { return parseInt(v, 10); }));

        function updateSelection(e) {
            var selected = Array.from(checkboxes).filter(function (cb) { return cb.checked; }).map(function (cb) { return cb.value; });
            if (selected.length > MAX_SELECTION) {
                e.target.checked = false;
                alert('مسموح باختيار موظفين فقط كحد أقصى.');
                return;
            }
            try {
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(selected));
            } catch (err) {}
            sendSelectedToSession(selected.map(function (v) { return parseInt(v, 10); }));
        }

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', updateSelection);
        });
    }

    document.addEventListener('DOMContentLoaded', initEmployeeSelectionLimit);
})();

