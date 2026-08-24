<?php

use App\Support\ResultFilterVariants;

it('allows حجب as a result option in the grades catalog', function () {
    $options = require dirname(__DIR__, 2).'/config/grades_catalog.php';

    expect($options['result_options'])->toContain('حجب');
});

it('filters حجب as an exact single result value', function () {
    expect(ResultFilterVariants::expand('حجب'))->toBe(['حجب']);
});
