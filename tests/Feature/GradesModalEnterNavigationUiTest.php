<?php

it('wires enter-key field tracking in the grades modal script', function () {
    $js = file_get_contents(public_path('js/students/index.js'));

    expect($js)->toContain('gradesFieldOrder')
        ->and($js)->toContain('gradesSummaryFieldOrder')
        ->and($js)->toContain('grades-enrollment-number-input')
        ->and($js)->toContain('grades-issuing-authority-input')
        ->and($js)->toContain('grades-total-input')
        ->and($js)->toContain('grades-average-input')
        ->and($js)->toContain('grades-result-input')
        ->and($js)->toContain('grades-round-input')
        ->and($js)->toContain('grades-row-score')
        ->and($js)->toContain('focusNextGradeScore')
        ->and($js)->toContain('focusFirstSummaryOrSave')
        ->and($js)->toContain('focusNextSummaryField')
        ->and($js)->toContain('focusFirstEditableField')
        ->and($js)->toContain("e.key !== 'Enter'")
        ->and($js)->toContain('grades-btn-save')
        ->and($js)->toContain("target.classList.contains('grades-row-subject')")
        ->and($js)->toContain('el.tabIndex = -1');
});
