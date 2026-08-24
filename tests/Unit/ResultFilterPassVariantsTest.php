<?php

use App\Support\ResultFilterVariants;

it('expands ناجحون filter to all pass variants', function () {
    expect(ResultFilterVariants::expand('ناجحون'))->toBe(ResultFilterVariants::PASS_VARIANTS)
        ->and(ResultFilterVariants::expand('ناجح'))->toBe(ResultFilterVariants::PASS_VARIANTS)
        ->and(ResultFilterVariants::expand('ناجحة'))->toBe(ResultFilterVariants::PASS_VARIANTS)
        ->and(ResultFilterVariants::expand(ResultFilterVariants::PASS_VARIANTS[2]))->toBe(ResultFilterVariants::PASS_VARIANTS);
});

it('expands معيدون filter to all repeat variants', function () {
    expect(ResultFilterVariants::expand('معيدون'))->toBe(ResultFilterVariants::REPEAT_VARIANTS)
        ->and(ResultFilterVariants::expand('معيد'))->toBe(ResultFilterVariants::REPEAT_VARIANTS)
        ->and(ResultFilterVariants::expand('معيدة'))->toBe(ResultFilterVariants::REPEAT_VARIANTS)
        ->and(ResultFilterVariants::expand(ResultFilterVariants::REPEAT_VARIANTS[2]))->toBe(ResultFilterVariants::REPEAT_VARIANTS);
});

it('expands راسبون filter to all fail variants', function () {
    expect(ResultFilterVariants::expand('راسبون'))->toBe(ResultFilterVariants::FAIL_VARIANTS)
        ->and(ResultFilterVariants::expand('راسب'))->toBe(ResultFilterVariants::FAIL_VARIANTS)
        ->and(ResultFilterVariants::expand('راسبة'))->toBe(ResultFilterVariants::FAIL_VARIANTS)
        ->and(ResultFilterVariants::expand(ResultFilterVariants::FAIL_VARIANTS[2]))->toBe(ResultFilterVariants::FAIL_VARIANTS);
});

it('expands حجب filter as a single value', function () {
    expect(ResultFilterVariants::expand('حجب'))->toBe(['حجب']);
});

it('returns empty array for blank result filter', function () {
    expect(ResultFilterVariants::expand(''))->toBe([]);
});

it('exposes grouped result filter options', function () {
    expect(ResultFilterVariants::filterOptions())->toBe([
        'ناجحون',
        'معيدون',
        'راسبون',
        'حجب',
    ]);
});

it('resolves legacy single values to grouped filter labels', function () {
    expect(ResultFilterVariants::resolveFilterOption('ناجح'))->toBe('ناجحون')
        ->and(ResultFilterVariants::resolveFilterOption('معيدة'))->toBe('معيدون')
        ->and(ResultFilterVariants::resolveFilterOption('راسب'))->toBe('راسبون')
        ->and(ResultFilterVariants::resolveFilterOption('حجب'))->toBe('حجب')
        ->and(ResultFilterVariants::resolveFilterOption(''))->toBe('');
});

it('allows pass variants in grades catalog result options', function () {
    $options = require dirname(__DIR__, 2).'/config/grades_catalog.php';

    foreach (ResultFilterVariants::PASS_VARIANTS as $variant) {
        expect($options['result_options'])->toContain($variant);
    }
});
