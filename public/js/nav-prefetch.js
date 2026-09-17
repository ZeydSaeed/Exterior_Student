/**
 * Prefetch/prerender same-origin pages so sidebar navigation feels instant.
 * بيانات الطلبة is warmed on dashboard load, not only after hover.
 * Does not change layout, styles, or navigation behavior.
 */
(function () {
    'use strict';

    var hoverTimer = null;
    var prefetched = {};

    function isPrefetchable(href) {
        if (!href || href === '#' || href.indexOf('javascript:') === 0) {
            return false;
        }

        try {
            var url = new URL(href, window.location.href);

            return url.origin === window.location.origin
                && (url.protocol === 'http:' || url.protocol === 'https:');
        } catch (e) {
            return false;
        }
    }

    function prefetch(href) {
        if (prefetched[href]) {
            return;
        }

        prefetched[href] = true;
        var link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = href;
        document.head.appendChild(link);
    }

    function warmupStudentsLink() {
        var studentsLink = document.querySelector('a[aria-label="بيانات الطلبة"]');
        if (!studentsLink || !isPrefetchable(studentsLink.href)) {
            return;
        }

        if (studentsLink.getAttribute('aria-current') === 'page') {
            return;
        }

        prefetch(studentsLink.href);

        if (document.querySelector('script[type="speculationrules"]')) {
            return;
        }

        if (window.fetch) {
            fetch(studentsLink.href, {
                credentials: 'same-origin',
                method: 'GET',
                priority: 'low'
            }).catch(function () {});
        }
    }

    document.addEventListener('mouseover', function (e) {
        var anchor = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!anchor || !isPrefetchable(anchor.href)) {
            return;
        }

        window.clearTimeout(hoverTimer);
        hoverTimer = window.setTimeout(function () {
            prefetch(anchor.href);
        }, 65);
    });

    document.addEventListener('mouseout', function () {
        window.clearTimeout(hoverTimer);
    });

    document.addEventListener('pointerdown', function (e) {
        var anchor = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!anchor || !isPrefetchable(anchor.href)) {
            return;
        }

        window.clearTimeout(hoverTimer);
        prefetch(anchor.href);
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', warmupStudentsLink);
    } else {
        warmupStudentsLink();
    }
})();
