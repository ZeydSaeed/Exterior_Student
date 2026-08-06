<?php

it('requires selecting a round before uploading results excel', function () {
    $response = $this->from(route('students.results-import-excel'))
        ->post(route('students.results-import-excel.upload'), []);

    $response->assertRedirect(route('students.results-import-excel'))
        ->assertSessionHas('app_dialog');

    $dialog = session('app_dialog');
    expect($dialog)->toBeArray()
        ->and((string) ($dialog['message'] ?? ''))->not->toBe('');
});

it('rejects an invalid round value on results excel upload', function () {
    $response = $this->from(route('students.results-import-excel'))
        ->post(route('students.results-import-excel.upload'), [
            'round' => 'دور غير موجود',
        ]);

    $response->assertRedirect(route('students.results-import-excel'))
        ->assertSessionHas('app_dialog');

    $dialog = session('app_dialog');
    expect($dialog)->toBeArray()
        ->and((string) ($dialog['message'] ?? ''))->toContain('الدور');
});

it('exposes the expected round options for results import', function () {
    expect(config('grades_catalog.round_options'))->toBe([
        'الاول',
        'الاول تكميلي',
        'الثاني',
        'الثاني تكميلي',
        'الثالث',
        'الثالث تكميلي',
    ]);
});
