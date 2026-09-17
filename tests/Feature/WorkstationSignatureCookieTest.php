<?php

use App\Http\Middleware\AssignWorkstationId;

it('sets a workstation cookie on dashboard without changing layout markup', function () {
    $response = $this->get(route('dashboard'));

    $response->assertSuccessful()
        ->assertCookie(AssignWorkstationId::COOKIE);

    $layout = (string) file_get_contents(resource_path('views/employees/index.blade.php'));

    expect($layout)->toContain('منظم التاييد')
        ->and($layout)->toContain('المسؤول')
        ->and($layout)->toContain('right_signature')
        ->and($layout)->toContain('left_signature');
});

it('reuses the same workstation cookie sent by the computer', function () {
    $id = 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee';

    $this->withCookie(AssignWorkstationId::COOKIE, $id)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertCookie(AssignWorkstationId::COOKIE, $id);
});
