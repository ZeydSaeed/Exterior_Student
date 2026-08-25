<?php

it('shows a notes column in the documents table before the actions column', function () {
    $view = file_get_contents(resource_path('views/student-records/index.blade.php'));
    $headers = strstr($view, 'جدول الوثائق');

    expect($headers)->toContain('<th>الملاحظات</th>')
        ->and($headers)->toMatch('/<th>الملاحظات<\/th>\s*<th>إجراءات<\/th>/')
        ->and($view)->not->toContain('document-notes-row');
});

it('shows the add-form notes below the document number fields spanning number date and addressee', function () {
    $view = file_get_contents(resource_path('views/student-records/index.blade.php'));
    $css = file_get_contents(public_path('css/dashboard.css'));

    expect($view)->toContain('document-add-notes')
        ->and($view)->toContain('grid-column: 1 / 4')
        ->and($view)->toContain('aria-label="الملاحظات"')
        ->and($view)->not->toContain('<label')
        ->and($css)->toContain('grid-column: 1 / 4')
        ->and($css)->toContain('min-height: 2.15rem');
});
