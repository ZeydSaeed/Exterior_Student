<?php

use App\Support\AcademicYearOptions;

it('builds academic years from 1990 to 2040 in YYYY-YYYY format oldest first', function () {
    $years = AcademicYearOptions::all();

    expect($years[0])->toBe('1990-1991')
        ->and($years[array_key_last($years)])->toBe('2040-2041')
        ->and($years)->toContain('2026-2027')
        ->and(count($years))->toBe(2040 - 1990 + 1);
});

it('validates academic year labels in the supported range', function () {
    expect(AcademicYearOptions::isValid('2026-2027'))->toBeTrue()
        ->and(AcademicYearOptions::isValid('1989-1990'))->toBeFalse()
        ->and(AcademicYearOptions::isValid('2041-2042'))->toBeFalse();
});
