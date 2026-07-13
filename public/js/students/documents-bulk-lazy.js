/**
 * تحميل تدريجي لصفحة القيود الدفعية + تجهيز كامل قبل الطباعة مع شريط تقدم.
 */
(function () {
    'use strict';

    var root = document.getElementById('documents-bulk-root');
    if (!root) {
        return;
    }

    var chunkUrl = root.getAttribute('data-chunk-url') || '';
    var chunkSize = parseInt(root.getAttribute('data-chunk-size') || '5', 10);
    if (chunkSize < 1) {
        chunkSize = 5;
    }

    var totalCount = parseInt(root.getAttribute('data-total') || '0', 10);
    var loadingIds = new Set();
    var scrollObserver = null;
    var isPrinting = false;

    var printBtn = document.getElementById('documents-bulk-btn-print');
    var progressOverlay = document.getElementById('documents-bulk-progress');
    var progressFill = document.getElementById('documents-bulk-progress-fill');
    var progressText = document.getElementById('documents-bulk-progress-text');

    function getPlaceholders() {
        return Array.prototype.slice.call(root.querySelectorAll('.doc-bulk-placeholder[data-loaded="0"]'));
    }

    function getLoadedCount() {
        return root.querySelectorAll('.doc-bulk-loaded').length;
    }

    function isLoaded(studentId) {
        return root.querySelector('.doc-bulk-loaded[data-student-id="' + studentId + '"]') !== null;
    }

    function updateProgress(loaded, total, message) {
        if (!progressOverlay || !progressFill || !progressText) {
            return;
        }
        var pct = total > 0 ? Math.round((loaded / total) * 100) : 0;
        progressFill.style.width = pct + '%';
        progressText.textContent = message || (loaded + ' / ' + total);
    }

    function showProgress(title) {
        if (!progressOverlay) {
            return;
        }
        var titleEl = document.getElementById('documents-bulk-progress-title');
        if (titleEl && title) {
            titleEl.textContent = title;
        }
        progressOverlay.removeAttribute('hidden');
        progressOverlay.setAttribute('aria-hidden', 'false');
    }

    function hideProgress() {
        if (!progressOverlay) {
            return;
        }
        progressOverlay.setAttribute('hidden', 'hidden');
        progressOverlay.setAttribute('aria-hidden', 'true');
    }

    function setPrintBusy(busy) {
        isPrinting = busy;
        if (printBtn) {
            printBtn.disabled = busy;
        }
    }

    function fetchChunk(ids) {
        if (!chunkUrl || ids.length === 0) {
            return Promise.resolve('');
        }

        var pending = ids.filter(function (id) {
            return !loadingIds.has(id) && !isLoaded(String(id));
        });

        if (pending.length === 0) {
            return Promise.resolve('');
        }

        pending.forEach(function (id) {
            loadingIds.add(id);
        });

        pending.forEach(function (id) {
            var ph = root.querySelector('.doc-bulk-placeholder[data-student-id="' + id + '"]');
            if (ph) {
                ph.classList.add('is-loading');
                var inner = ph.querySelector('.doc-bulk-placeholder-inner');
                if (inner) {
                    inner.textContent = 'جاري التحميل...';
                }
            }
        });

        var url = chunkUrl + (chunkUrl.indexOf('?') === -1 ? '?' : '&') + 'ids=' + encodeURIComponent(pending.join(','));

        return fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        })
            .then(function (response) {
                if (response.status === 204) {
                    return '';
                }
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.text();
            })
            .then(function (html) {
                if (html && html.trim() !== '') {
                    applyChunkHtml(html);
                    if (window.ArabicDate && typeof window.ArabicDate.init === 'function') {
                        window.ArabicDate.init();
                    }
                }
            })
            .catch(function () {
                pending.forEach(function (id) {
                    var ph = root.querySelector('.doc-bulk-placeholder[data-student-id="' + id + '"]');
                    if (ph) {
                        ph.classList.remove('is-loading');
                        var inner = ph.querySelector('.doc-bulk-placeholder-inner');
                        if (inner) {
                            inner.textContent = 'تعذر التحميل';
                        }
                    }
                });
            })
            .finally(function () {
                pending.forEach(function (id) {
                    loadingIds.delete(id);
                });
            });
    }

    function applyChunkHtml(html) {
        var temp = document.createElement('div');
        temp.innerHTML = html.trim();
        var items = temp.querySelectorAll('.doc-bulk-loaded[data-student-id]');

        items.forEach(function (item) {
            var studentId = item.getAttribute('data-student-id');
            if (!studentId) {
                return;
            }
            var placeholder = root.querySelector('.doc-bulk-placeholder[data-student-id="' + studentId + '"]');
            if (placeholder) {
                placeholder.replaceWith(item);
            }
        });
    }

    function idsNearElement(el) {
        var index = parseInt(el.getAttribute('data-index') || '0', 10);
        var placeholders = getPlaceholders();
        var nearby = placeholders
            .map(function (ph) {
                return {
                    id: parseInt(ph.getAttribute('data-student-id') || '0', 10),
                    index: parseInt(ph.getAttribute('data-index') || '0', 10),
                };
            })
            .filter(function (row) {
                return row.id > 0 && Math.abs(row.index - index) <= 3;
            })
            .sort(function (a, b) {
                return a.index - b.index;
            })
            .slice(0, chunkSize)
            .map(function (row) {
                return row.id;
            });

        return nearby;
    }

    function loadVisibleChunks() {
        if (isPrinting) {
            return;
        }

        var placeholders = getPlaceholders();
        if (placeholders.length === 0) {
            return;
        }

        var viewportTop = window.scrollY || document.documentElement.scrollTop;
        var viewportBottom = viewportTop + window.innerHeight;
        var target = null;

        placeholders.forEach(function (ph) {
            var rect = ph.getBoundingClientRect();
            var top = rect.top + viewportTop;
            var bottom = top + rect.height;
            if (bottom >= viewportTop - 200 && top <= viewportBottom + 400) {
                target = ph;
            }
        });

        if (!target) {
            return;
        }

        var ids = idsNearElement(target);
        if (ids.length > 0) {
            fetchChunk(ids);
        }
    }

    function installScrollObserver() {
        if (!('IntersectionObserver' in window)) {
            window.addEventListener('scroll', throttle(loadVisibleChunks, 200), { passive: true });
            return;
        }

        scrollObserver = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || isPrinting) {
                        return;
                    }
                    var ids = idsNearElement(entry.target);
                    if (ids.length > 0) {
                        fetchChunk(ids);
                    }
                });
            },
            { root: null, rootMargin: '400px 0px', threshold: 0 }
        );

        getPlaceholders().forEach(function (ph) {
            scrollObserver.observe(ph);
        });
    }

    function observeNewPlaceholders() {
        if (!scrollObserver) {
            return;
        }
        getPlaceholders().forEach(function (ph) {
            scrollObserver.observe(ph);
        });
    }

    function throttle(fn, wait) {
        var timer = null;
        return function () {
            if (timer) {
                return;
            }
            timer = setTimeout(function () {
                timer = null;
                fn();
            }, wait);
        };
    }

    function loadAllForPrint() {
        var placeholders = getPlaceholders();
        if (placeholders.length === 0) {
            return Promise.resolve();
        }

        var allIds = placeholders
            .map(function (ph) {
                return parseInt(ph.getAttribute('data-student-id') || '0', 10);
            })
            .filter(function (id) {
                return id > 0;
            });

        var batches = [];
        for (var i = 0; i < allIds.length; i += chunkSize) {
            batches.push(allIds.slice(i, i + chunkSize));
        }

        var loadedBefore = getLoadedCount();
        var toLoad = allIds.length;

        showProgress('جاري تجهيز القيود للطباعة');
        updateProgress(loadedBefore, totalCount, loadedBefore + ' / ' + totalCount);

        var chain = Promise.resolve();
        var loadedInRun = 0;

        batches.forEach(function (batch) {
            chain = chain.then(function () {
                return fetchChunk(batch).then(function () {
                    loadedInRun += batch.length;
                    var current = getLoadedCount();
                    updateProgress(current, totalCount, current + ' / ' + totalCount);
                });
            });
        });

        return chain.then(function () {
            updateProgress(getLoadedCount(), totalCount, getLoadedCount() + ' / ' + totalCount);
            if (getPlaceholders().length > 0 && loadedInRun < toLoad) {
                return loadAllForPrint();
            }
        });
    }

    function handlePrint() {
        if (!printBtn || typeof window.print !== 'function') {
            return;
        }

        setPrintBusy(true);

        loadAllForPrint()
            .then(function () {
                hideProgress();
                setTimeout(function () {
                    window.print();
                }, 150);
            })
            .catch(function () {
                hideProgress();
                window.alert('تعذر تحميل بعض القيود. يرجى المحاولة مرة أخرى.');
            })
            .finally(function () {
                setPrintBusy(false);
            });
    }

    if (printBtn) {
        printBtn.addEventListener('click', handlePrint);
    }

    installScrollObserver();

    var mutationTimer = null;
    var layoutObserver = new MutationObserver(function () {
        if (mutationTimer) {
            clearTimeout(mutationTimer);
        }
        mutationTimer = setTimeout(observeNewPlaceholders, 100);
    });
    layoutObserver.observe(root, { childList: true, subtree: true });

    loadVisibleChunks();
})();
