<?php

it('places the logout control beside the signed-in user name', function () {
    $html = (string) file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    $userNamePos = strpos($html, 'dashboard-toolbar-user-name');
    $logoutPos = strpos($html, 'dashboard-toolbar-logout');
    $accountsPos = strpos($html, 'accounts.index');

    expect($html)->toContain('dashboard-toolbar-user')
        ->and($html)->toContain('route(\'logout\')')
        ->and($userNamePos)->not->toBeFalse()
        ->and($logoutPos)->not->toBeFalse()
        ->and($accountsPos)->not->toBeFalse()
        ->and($logoutPos)->toBeGreaterThan($userNamePos)
        ->and($accountsPos)->toBeGreaterThan($logoutPos);
});

it('keeps toolbar icons on a single row', function () {
    $css = (string) file_get_contents(public_path('css/dashboard.css'));

    expect($css)->toContain('.dashboard-toolbar-group {')
        ->and($css)->toContain('flex-wrap: nowrap')
        ->and($css)->toContain('grid-template-columns: auto minmax(0, 1fr) auto')
        ->and($css)->toContain('.dashboard-toolbar-logout');
});

it('shows the user name and logout control on the dashboard toolbar', function () {
    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('المسؤول')
        ->assertSee('خروج')
        ->assertSee('dashboard-toolbar-logout', false);
});

it('places the ems logo in the sidebar and program title, not the toolbar', function () {
    $layout = (string) file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    $home = (string) file_get_contents(resource_path('views/dashboard.blade.php'));
    $login = (string) file_get_contents(resource_path('views/auth/login.blade.php'));
    $css = (string) file_get_contents(public_path('css/dashboard.css'));

    expect($layout)->toContain('dashboard-sidebar-brand')
        ->and($layout)->toContain('images/ems-logo.svg')
        ->and($layout)->not->toContain('class="dashboard-toolbar-logo"')
        ->and($home)->toContain('app-title-logo')
        ->and($login)->toContain('login-brand-logo')
        ->and($css)->toContain('.dashboard-sidebar-brand')
        ->and($css)->toContain('width: 1.65rem')
        ->and($css)->not->toContain('.dashboard-toolbar-logo {')
        ->and($layout)->toContain("asset('favicon.ico')")
        ->and($layout)->toContain("asset('icon-students-16.png')")
        ->and($layout)->toContain("asset('favicon-mark.svg')")
        ->and($login)->toContain("asset('favicon.ico')")
        ->and($login)->toContain("asset('icon-students-16.png')")
        ->and(file_exists(public_path('favicon.ico')))->toBeTrue()
        ->and(file_exists(public_path('icon-students.ico')))->toBeTrue()
        ->and(file_exists(public_path('icon-students-16.png')))->toBeTrue()
        ->and(file_exists(public_path('favicon-mark.svg')))->toBeTrue();

    expect(getimagesize(public_path('icon-students-16.png'))[0])->toBe(16)
        ->and(getimagesize(public_path('icon-students-32.png'))[0])->toBe(32);

    $icon16 = (string) file_get_contents(public_path('icon-students-16.png'));
    $sample = imagecreatefrompng(public_path('icon-students-16.png'));
    $cornerAlpha = (imagecolorat($sample, 0, 0) >> 24) & 0x7F;
    imagedestroy($sample);

    expect(ord($icon16[25]))->toBe(6)
        ->and($cornerAlpha)->toBe(127);
});

it('renders the ems logo on the dashboard', function () {
    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('dashboard-sidebar-brand', false)
        ->assertSee('app-title-logo', false)
        ->assertSee('images/ems-logo.svg', false)
        ->assertSee('icon-students-16.png', false)
        ->assertSee('favicon-mark.svg', false)
        ->assertDontSee('class="dashboard-toolbar-logo"', false);
});
