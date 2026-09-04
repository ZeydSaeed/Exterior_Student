<?php

it('wires enter-key tracking on the documents page and submits add on enter', function () {
    $view = file_get_contents(resource_path('views/student-records/index.blade.php'));
    $js = file_get_contents(public_path('js/students/documents-enter-nav.js'));

    expect($view)->toContain('documents-enter-nav.js')
        ->and($view)->toContain('id="document-add-number"')
        ->and($view)->toContain('id="document-add-submit"')
        ->and($view)->toContain('documents-next-btn')
        ->and($js)->toContain('document-add-submit')
        ->and($js)->toContain('requestSubmit')
        ->and($js)->toContain('focusNextButton')
        ->and($js)->toContain("e.key !== 'Enter'")
        ->and($js)->not->toContain('if (!focusNextButton())');
});
