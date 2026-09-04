<?php

it('shows the logged-in account name on certificates for print only', function () {
    $withoutGrades = file_get_contents(resource_path('views/students/certificate.blade.php'));
    $withGrades = file_get_contents(resource_path('views/students/certificate-with-grades.blade.php'));
    $withoutCss = file_get_contents(public_path('css/certificate.css'));
    $withCss = file_get_contents(public_path('css/certificate-with-grades.css'));

    expect($withoutGrades)->toContain('certificate-account-name')
        ->and($withoutGrades)->toContain('auth()->user()->name')
        ->and($withGrades)->toContain('certificate-account-name')
        ->and($withGrades)->toContain('auth()->user()->name')
        ->and($withoutCss)->toContain('.certificate-account-name')
        ->and($withoutCss)->toContain('display: none')
        ->and($withoutCss)->toContain('font-size: 8pt')
        ->and($withoutCss)->toContain('bottom: 0.35cm')
        ->and($withoutCss)->toContain('right: 1cm')
        ->and($withCss)->toContain('.certificate-account-name')
        ->and($withCss)->toContain('font-size: 8pt')
        ->and($withCss)->toContain('right: 1cm');
});
