(function () {
    function fitImportExcelTables() {
        var wraps = document.querySelectorAll('.import-excel-table-wrap');
        wraps.forEach(function (wrap) {
            var table = wrap.querySelector('.import-excel-table');
            if (!table || wrap.clientWidth <= 0) {
                return;
            }

            table.style.fontSize = '';
            table.style.transform = '';
            wrap.style.height = '';

            var size = parseFloat(window.getComputedStyle(table).fontSize);
            var minSize = 8;
            var guard = 0;
            while (table.scrollWidth > wrap.clientWidth + 1 && size > minSize && guard < 48) {
                size -= 0.4;
                table.style.fontSize = size + 'px';
                guard += 1;
            }

            if (table.scrollWidth > wrap.clientWidth + 1) {
                var scale = wrap.clientWidth / table.scrollWidth;
                table.style.transformOrigin = 'top right';
                table.style.transform = 'scale(' + scale + ')';
                wrap.style.height = Math.ceil(table.getBoundingClientRect().height) + 'px';
            }
        });
    }

    window.addEventListener('resize', fitImportExcelTables);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fitImportExcelTables);
    } else {
        fitImportExcelTables();
    }
})();
