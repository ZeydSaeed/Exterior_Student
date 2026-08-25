<?php

it('sizes the addressee and purpose fields to fit the student documents row', function () {
    $view = file_get_contents(resource_path('views/student-records/index.blade.php'));
    $css = file_get_contents(public_path('css/dashboard.css'));

    expect($view)->toContain('doc-field-addressee')
        ->and($view)->toContain('doc-field-purpose')
        ->and($view)->toContain('min-width: 28rem')
        ->and($view)->toContain('min-width: 12rem')
        ->and($view)->toContain('minmax(28rem, 2fr)')
        ->and($view)->toContain('minmax(12rem, 1fr)')
        ->and($css)->toContain('input.doc-field-purpose')
        ->and($css)->toContain('min-width: 28rem')
        ->and($css)->toContain('min-width: 12rem');
});
