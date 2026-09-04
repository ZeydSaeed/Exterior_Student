<?php

use App\Support\GenderFilterVariants;

it('expands female gender filter to both spellings', function () {
    expect(GenderFilterVariants::expand('انثى'))->toBe(GenderFilterVariants::FEMALE_VARIANTS)
        ->and(GenderFilterVariants::expand('أنثى'))->toBe(GenderFilterVariants::FEMALE_VARIANTS)
        ->and(GenderFilterVariants::expand('ذكر'))->toBe(['ذكر'])
        ->and(GenderFilterVariants::expand(''))->toBe([]);
});

it('displays a single female label without hamza', function () {
    expect(GenderFilterVariants::displayLabel('أنثى'))->toBe('انثى')
        ->and(GenderFilterVariants::displayLabel('انثى'))->toBe('انثى')
        ->and(GenderFilterVariants::displayLabel('ذكر'))->toBe('ذكر');
});

it('normalizes gender filter options to one female entry', function () {
    $options = GenderFilterVariants::normalizeOptions(['ذكر', 'أنثى', 'انثى', 'أنثى', '']);

    expect($options->all())->toBe(['ذكر', 'انثى']);
});
