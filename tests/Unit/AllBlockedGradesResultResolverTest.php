<?php

use App\Application\Student\Service\AllBlockedGradesResultResolver;
use App\Domain\Student\SubjectCatalogInterface;

it('forces result حجب when every catalog subject score is حجب', function () {
    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')
        ->with('الصناعي', 'كهرباء')
        ->andReturn(['اللغة العربية', 'الرياضيات', 'التدريب العملي']);

    $resolver = new AllBlockedGradesResultResolver($catalog);

    $payload = $resolver->applyToPayload([
        'branch' => 'الصناعي',
        'major' => 'كهرباء',
        'result' => 'ناجح',
        'grades' => [
            ['subject' => 'اللغة العربية', 'score' => 'حجب'],
            ['subject' => 'الرياضيات', 'score' => 'حجب'],
            ['subject' => 'التدريب العملي', 'score' => 'حجب'],
        ],
    ]);

    expect($payload['result'])->toBe('حجب')
        ->and($resolver->shouldForceBlockedResult('الصناعي', 'كهرباء', $payload['grades']))->toBeTrue();
});

it('does not force حجب when any subject has another score', function () {
    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')
        ->with('الصناعي', 'كهرباء')
        ->andReturn(['اللغة العربية', 'الرياضيات']);

    $resolver = new AllBlockedGradesResultResolver($catalog);

    $payload = $resolver->applyToPayload([
        'branch' => 'الصناعي',
        'major' => 'كهرباء',
        'result' => 'ناجح',
        'grades' => [
            ['subject' => 'اللغة العربية', 'score' => 'حجب'],
            ['subject' => 'الرياضيات', 'score' => '80'],
        ],
    ]);

    expect($payload['result'])->toBe('ناجح')
        ->and($resolver->shouldForceBlockedResult('الصناعي', 'كهرباء', $payload['grades']))->toBeFalse();
});

it('does not force حجب when a catalog subject score is missing', function () {
    $catalog = Mockery::mock(SubjectCatalogInterface::class);
    $catalog->shouldReceive('getSubjectsFor')
        ->once()
        ->with('الصناعي', 'كهرباء')
        ->andReturn(['اللغة العربية', 'الرياضيات']);

    $resolver = new AllBlockedGradesResultResolver($catalog);

    expect($resolver->shouldForceBlockedResult('الصناعي', 'كهرباء', [
        ['subject' => 'اللغة العربية', 'score' => 'حجب'],
    ]))->toBeFalse();
});
