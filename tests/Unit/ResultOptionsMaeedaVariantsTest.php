<?php

it('allows both معيده and معيدة as result options', function () {
    $options = require dirname(__DIR__, 2).'/config/grades_catalog.php';

    expect($options['result_options'])->toContain('معيده')
        ->and($options['result_options'])->toContain('معيدة');
});

it('keeps معيده and معيدة as distinct allowed result values', function () {
    $options = require dirname(__DIR__, 2).'/config/grades_catalog.php';

    expect($options['result_options'])->toContain('معيد')
        ->and(in_array('معيده', $options['result_options'], true))->toBeTrue()
        ->and(in_array('معيدة', $options['result_options'], true))->toBeTrue()
        ->and('معيده')->not->toBe('معيدة');
});
