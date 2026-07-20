<?php

use App\Support\ArabicDigits;

it('converts western digits to arabic', function () {
    expect(ArabicDigits::toArabic('18818'))->toBe('١٨٨١٨');
});

it('converts arabic digits to western', function () {
    expect(ArabicDigits::toWestern('١٨٨١٨'))->toBe('18818');
    expect(ArabicDigits::toWestern('١٥ / ٦ / ٢٠٠٦'))->toBe('15 / 6 / 2006');
});
