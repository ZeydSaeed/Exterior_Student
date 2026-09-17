<?php

use App\Application\Student\Import\ImportStudentResultsFromExcelUseCase;

/**
 * @return list<string>
 */
function expectedResultsExcelColumnLabels(): array
{
    return [
        'الرقم الامتحاني',
        'اسم الطالب',
        'الفرع',
        'الاختصاص',
        'العام الدراسي',
        'المادة 1',
        'المادة 2',
        'المادة 3',
        'المادة 4',
        'المادة 5',
        'المادة 6',
        'المادة 7',
        'المادة 8',
        'المجموع',
        'المعدل',
        'النتيجة',
    ];
}

it('exposes results excel columns in spreadsheet order', function () {
    $columns = app(ImportStudentResultsFromExcelUseCase::class)->excelColumnOrder();

    expect(array_column($columns, 'label'))->toBe(expectedResultsExcelColumnLabels())
        ->and(array_column($columns, 'key'))->toBe([
            'exam_number',
            'student_name',
            'branch',
            'major',
            'academic_year',
            'subject_1',
            'subject_2',
            'subject_3',
            'subject_4',
            'subject_5',
            'subject_6',
            'subject_7',
            'subject_8',
            'total',
            'average',
            'result',
        ]);
});

it('renders the results import page columns as an excel-style table in the expected order', function () {
    $this->get(route('students.results-import-excel'))
        ->assertSuccessful()
        ->assertSee('import-excel-columns-table', false)
        ->assertSee('import-excel-table-wrap', false)
        ->assertSee('import-excel-fit.js', false)
        ->assertSee('data-fit-table="true"', false)
        ->assertSee('يجب ان تكون المطابقة دقيقة وبدون اي اختلاف', false)
        ->assertSeeInOrder(expectedResultsExcelColumnLabels());
});

it('keeps results preview table headers in the same excel column order', function () {
    $html = (string) file_get_contents(resource_path('views/students/import-results-excel-preview.blade.php'));

    expect($html)->toContain('import-excel-columns-table')
        ->and($html)->toContain('@foreach($excelColumns as $column)')
        ->and($html)->toContain('$row->{$column[\'key\']}')
        ->and($html)->toContain('import-excel-fit.js');
});
