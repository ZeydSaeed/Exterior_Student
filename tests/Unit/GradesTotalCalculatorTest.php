<?php

use App\Application\Student\Service\GradesTotalCalculator;

it('sums numeric subject scores as integers', function () {
    $calculator = new GradesTotalCalculator;

    expect($calculator->sum([
        ['subject' => 'رياضيات', 'score' => '80'],
        ['subject' => 'فيزياء', 'score' => '70.4'],
        ['subject' => 'كيمياء', 'score' => '60'],
    ]))->toBe(210);
});

it('treats empty and non-numeric scores as zero', function () {
    $calculator = new GradesTotalCalculator;

    expect($calculator->sum([
        ['subject' => 'رياضيات', 'score' => ''],
        ['subject' => 'فيزياء', 'score' => 'abc'],
        ['subject' => 'كيمياء', 'score' => '50'],
        ['subject' => 'لغة', 'score' => null],
    ]))->toBe(50);
});

it('returns zero for an empty grades list', function () {
    expect((new GradesTotalCalculator)->sum([]))->toBe(0);
});
