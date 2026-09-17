/**
 * Fast students-list filtering: swap table + summary without reloading layout/CSS/JS.
 * Falls back to a full navigation if the fragment request fails.
 */
(function () {
    'use strict';

    if (!document.body || !document.body.classList.contains('page-students') || document.body.classList.contains('page-repeaters')) {
        return;
    }

    var area = document.querySelector('.students-table-area');
    if (!area) {
        return;
    }

    var requestSeq = 0;
    var abortCtl = null;

    function toolbarSummary() {
        return document.querySelector('.dashboard-toolbar-filters-summary');
    }

    function applyFragment(html) {
        var wrap = document.createElement('div');
        wrap.innerHTML = html;
        var nextTable = wrap.querySelector('[data-students-fragment="table"]');
        var nextSummary = wrap.querySelector('[data-students-fragment="summary"] .dashboard-toolbar-filters-summary');
        if (!nextTable) {
            return false;
        }

        area.innerHTML = nextTable.innerHTML;
        var summary = toolbarSummary();
        if (summary && nextSummary) {
            summary.replaceWith(nextSummary);
        }

        return true;
    }

    function load(url, options) {
        options = options || {};
        if (!url) {
            return;
        }

        var seq = ++requestSeq;
        if (abortCtl) {
            abortCtl.abort();
        }
        abortCtl = typeof AbortController === 'function' ? new AbortController() : null;
        area.setAttribute('aria-busy', 'true');

        var fetchOpts = {
            credentials: 'same-origin',
            method: 'GET',
            headers: {
                'X-Students-List-Fragment': '1',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        };
        if (abortCtl) {
            fetchOpts.signal = abortCtl.signal;
        }

        fetch(url, fetchOpts).then(function (res) {
            if (!res.ok) {
                throw new Error('fragment');
            }
            return res.text();
        }).then(function (html) {
            if (seq !== requestSeq) {
                return;
            }
            if (!applyFragment(html)) {
                window.location = url;
                return;
            }
            if (!options.replace && window.history && window.history.pushState) {
                window.history.pushState({ studentsList: true }, '', url);
            }
        }).catch(function (err) {
            if (err && err.name === 'AbortError') {
                return;
            }
            window.location = url;
        }).then(function () {
            if (seq === requestSeq) {
                area.removeAttribute('aria-busy');
            }
        });
    }

    window.StudentsListFastFilter = {
        load: load
    };

    document.addEventListener('click', function (e) {
        var link = e.target && e.target.closest ? e.target.closest('.students-table-area .pagination a') : null;
        if (!link) {
            return;
        }
        var href = link.getAttribute('href');
        if (!href || href === '#') {
            return;
        }
        e.preventDefault();
        load(href);
    });

    document.addEventListener('submit', function (e) {
        var form = e.target && e.target.id === 'students-search-form' ? e.target : null;
        if (!form) {
            return;
        }
        e.preventDefault();
        var action = form.getAttribute('action') || window.location.pathname;
        var params = new URLSearchParams(new FormData(form));
        var query = params.toString();
        load(action + (query ? '?' + query : ''));
    });

    window.addEventListener('popstate', function () {
        load(window.location.href, { replace: true });
    });
})();
