<?php

it('locks documents table fields and exposes icon edit save delete actions', function () {
    $view = file_get_contents(resource_path('views/student-records/index.blade.php'));
    $table = strstr($view, 'جدول الوثائق');
    $js = file_get_contents(public_path('js/students/documents-row-edit.js'));

    expect($table)->toContain('data-documents-row')
        ->and($table)->toContain('documents-row-field')
        ->and($table)->toContain('readonly')
        ->and($table)->toContain('data-documents-edit')
        ->and($table)->toContain('data-documents-save')
        ->and($table)->toContain('aria-label="تعديل"')
        ->and($table)->toContain('aria-label="حفظ"')
        ->and($table)->toContain('aria-label="حذف"')
        ->and($table)->toContain('<svg')
        ->and($table)->not->toContain('>تعديل</button>')
        ->and($table)->not->toContain('>حفظ</button>')
        ->and($table)->not->toContain('>حذف</button>')
        ->and($table)->toContain('students.documents.update')
        ->and($table)->toContain('students.documents.destroy')
        ->and($view)->toContain('documents-row-edit.js')
        ->and($js)->toContain('submitRow')
        ->and($js)->toContain('data-documents-sync')
        ->and($js)->toContain('syncArabicDates')
        ->and($js)->toContain('textarea.documents-row-field[name="notes"]');
});
