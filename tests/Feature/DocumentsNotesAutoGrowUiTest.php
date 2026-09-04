<?php

it('auto-grows document notes without scrollbars', function () {
    $css = file_get_contents(public_path('css/dashboard.css'));
    $js = file_get_contents(public_path('js/students/documents-row-edit.js'));
    $view = file_get_contents(resource_path('views/student-records/index.blade.php'));

    expect($css)->toContain('.doc-field-notes')
        ->and($css)->toContain('overflow: hidden')
        ->and($css)->toContain('overflow-x: hidden')
        ->and($css)->toContain('overflow-y: hidden')
        ->and($css)->toContain('resize: none')
        ->and($js)->toContain('resizeNotesField')
        ->and($js)->toContain('initNotesAutoGrow')
        ->and($js)->toContain('scrollHeight')
        ->and($view)->toContain('overflow: hidden')
        ->and($view)->toContain('doc-field-notes');
});
