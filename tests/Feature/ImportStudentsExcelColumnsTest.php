<?php

use App\Application\Student\Import\ImportStudentsFromExcelUseCase;

/**
 * @return list<string>
 */
function expectedStudentExcelColumnLabels(): array
{
    return [
        'الرقم الامتحاني',
        'اسم الطالب',
        'اسم الاب',
        'اسم الجد',
        'اللقب',
        'الجنس',
        'التولد',
        'محل الولادة',
        'اسم الام الكامل',
        'الفرع',
        'الاختصاص',
        'العام الدراسي',
        'اخر مدرسة',
        'رقم الوثيقة',
        'تاريخها',
        'جهة الاصدار',
    ];
}

it('exposes student excel columns in spreadsheet order', function () {
    $columns = app(ImportStudentsFromExcelUseCase::class)->excelColumnOrder();

    expect(array_column($columns, 'label'))->toBe(expectedStudentExcelColumnLabels())
        ->and(array_column($columns, 'key'))->toBe([
            'exam_number',
            'first_name',
            'father',
            'grandfather',
            'last_name',
            'gender',
            'birth_date',
            'birth_place',
            'mother',
            'branch',
            'major',
            'academic_year',
            'last_school',
            'document_number',
            'document_date',
            'issue_place',
        ]);
});

it('renders the import page columns as an excel-style table in the expected order', function () {
    $this->get(route('students.import-excel'))
        ->assertSuccessful()
        ->assertSee('import-excel-columns-table', false)
        ->assertSee('import-excel-table-wrap', false)
        ->assertSee('import-excel-fit.js', false)
        ->assertSee('data-fit-table="true"', false)
        ->assertSee('صيغة التواريخ المقبولة: يوم/شهر/سنة مثل 15/06/2006 لحقل "التولد" و حقل "تاريخها"', false)
        ->assertSeeInOrder(expectedStudentExcelColumnLabels());
});

it('fits the import excel table to the page width without horizontal scrolling', function () {
    $css = (string) file_get_contents(public_path('css/dashboard.css'));
    $js = (string) file_get_contents(public_path('js/import-excel-fit.js'));
    $wrapBlock = preg_match('/\.import-excel-table-wrap \{[^}]+\}/s', $css, $wrapMatch)
        ? ($wrapMatch[0] ?? '')
        : '';
    $tableBlock = preg_match('/\.import-excel-table \{[^}]+\}/s', $css, $tableMatch)
        ? ($tableMatch[0] ?? '')
        : '';
    $cellBlock = preg_match('/\.import-excel-table th,\s*\.import-excel-table td \{[^}]+\}/s', $css, $cellMatch)
        ? ($cellMatch[0] ?? '')
        : '';

    expect($wrapBlock)->toContain('overflow-x: hidden')
        ->and($wrapBlock)->not->toContain('overflow-x: auto')
        ->and($tableBlock)->toContain('width: 100%')
        ->and($tableBlock)->toContain('table-layout: fixed')
        ->and($cellBlock)->toContain('white-space: normal')
        ->and($cellBlock)->toContain('min-width: 0')
        ->and($js)->toContain('fitImportExcelTables')
        ->and($js)->toContain('table.scrollWidth > wrap.clientWidth');
});

it('keeps preview table headers in the same excel column order', function () {
    $html = (string) file_get_contents(resource_path('views/students/import-excel-preview.blade.php'));

    expect($html)->toContain('import-excel-columns-table')
        ->and($html)->toContain('@foreach($excelColumns as $column)')
        ->and($html)->toContain('$row->{$column[\'key\']}');
});
