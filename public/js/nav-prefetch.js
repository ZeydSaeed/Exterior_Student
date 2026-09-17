/**
 * Prefetch same-origin pages on hover so sidebar/toolbar navigation feels instant.
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
})();
