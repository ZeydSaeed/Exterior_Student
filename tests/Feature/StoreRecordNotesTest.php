<?php

it('shows a notes column in the documents table before the actions column', function () {
    $view = file_get_contents(resource_path('views/student-records/index.blade.php'));
    $headers = strstr($view, 'جدول الوثائق');

    expect($headers)->toContain('<th>الملاحظات</th>')
        ->and($headers)->toMatch('/<th>الملاحظات<\/th>\s*<th>إجراءات<\/th>/')
        ->and($view)->not->toContain('document-notes-row');
});

it('does not show a notes field on the add-new-document form', function () {
    $view = file_get_contents(resource_path('views/student-records/index.blade.php'));
    $addForm = strstr($view, 'إضافة وثيقة جديدة');
    $addFormOnly = strstr($addForm, 'جدول الوثائق', true) ?: $addForm;

    expect($addFormOnly)->not->toContain('document-add-notes')
        ->and($addFormOnly)->not->toContain('name="notes"')
        ->and($addFormOnly)->not->toContain('placeholder="الملاحظات..."')
        ->and($view)->toContain('جدول الوثائق');
});
