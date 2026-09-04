<?php

it('matches signature date size and style with signature title on certificates', function () {
    $withoutGrades = file_get_contents(public_path('css/certificate.css'));
    $withGrades = file_get_contents(public_path('css/certificate-with-grades.css'));

    foreach ([$withoutGrades, $withGrades] as $css) {
        expect($css)->toMatch('/\.signature-title\s*\{[^}]*font-size:\s*0\.95rem/s')
            ->and($css)->toMatch('/\.signature-date\s*\{[^}]*font-size:\s*0\.95rem/s')
            ->and($css)->toMatch('/\.signature-date\s*\{[^}]*font-weight:\s*700/s')
            ->and($css)->toMatch('/\.signature-date\s*\{[^}]*font-family:\s*"Times New Roman"/s');
    }
});
