<?php

use App\Support\ImportDateNormalizer;

it('parses day/month/year with slashes like 25/4/1990', function (string $input, string $expected) {
    expect(ImportDateNormalizer::toYmd($input))->toBe($expected);
})->with([
    ['25/4/1990', '1990-04-25'],
    ['25/04/1990', '1990-04-25'],
    ['1/1/2000', '2000-01-01'],
    ['25-04-1990', '1990-04-25'],
    ['25.04.1990', '1990-04-25'],
    ['1990-04-25', '1990-04-25'],
    ['1990/04/25', '1990-04-25'],
]);

it('falls back to month/day/year when day/month is invalid', function () {
    expect(ImportDateNormalizer::toYmd('4/25/1990'))->toBe('1990-04-25');
});

it('returns null for empty or invalid values', function (mixed $input) {
    expect(ImportDateNormalizer::toYmd($input))->toBeNull();
})->with([
    [null],
    [''],
    ['   '],
    ['32/4/1990'],
    ['not-a-date'],
]);

it('formats display dates as day/month/year', function (mixed $input, string $expected) {
    expect(ImportDateNormalizer::toDisplayDmy($input))->toBe($expected);
})->with([
    ['2006-06-15', '15 / 06 / 2006'],
    ['2006-06-15 00:00:00', '15 / 06 / 2006'],
    ['15/06/2006', '15 / 06 / 2006'],
    ['15 / 06 / 2006', '15 / 06 / 2006'],
    ['25/4/1990', '25 / 04 / 1990'],
    [null, ''],
    ['', ''],
]);
