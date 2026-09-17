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

it('uses the same highlight colors for hover and selection on sidebar, toolbar, and filters', function () {
    $css = (string) file_get_contents(public_path('css/dashboard.css'));

    expect($css)->toContain('--dashboard-sidebar-hover-bg: #CAF0F8')
        ->and($css)->toContain('--dashboard-sidebar-hover-text: #03045E')
        ->and($css)->toContain('.dashboard-sidebar .dashboard-sidebar-link:hover,')
        ->and($css)->toContain('.dashboard-sidebar .dashboard-sidebar-link.is-active,')
        ->and($css)->toContain('.dashboard-toolbar-btn:hover,')
        ->and($css)->toContain('.dashboard-toolbar-btn.is-active,')
        ->and($css)->toContain('.dashboard-toolbar-employees:hover,')
        ->and($css)->toContain('.dashboard-toolbar-employees.is-active,')
        ->and($css)->toContain('.students-filter-card-options label:hover,')
        ->and($css)->toContain('.students-filter-card-options label:has(input:checked:not([value=""]))')
        ->and($css)->toContain('.students-filter-select:hover,')
        ->and($css)->toContain('.students-filter-select:has(option:checked:not([value=""]))')
        ->and($css)->toContain('background: var(--dashboard-sidebar-hover-bg)')
        ->and($css)->toContain('color: var(--dashboard-sidebar-hover-text)');
});

it('renders filter labels in black extra-bold type one step larger', function () {
    $css = (string) file_get_contents(public_path('css/dashboard.css'));
    $layout = (string) file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    $titleBlock = preg_match('/\.students-filter-card-title \{[^}]+\}/s', $css, $titleMatch)
        ? ($titleMatch[0] ?? '')
        : '';
    $selectBlock = preg_match('/\.students-filter-select \{[^}]+\}/s', $css, $selectMatch)
        ? ($selectMatch[0] ?? '')
        : '';
    $labelBlock = preg_match('/\.students-filter-card-options label \{[^}]+\}/s', $css, $labelMatch)
        ? ($labelMatch[0] ?? '')
        : '';

    $cardBlock = preg_match('/\.students-filter-card \{[^}]+\}/s', $css, $cardMatch)
        ? ($cardMatch[0] ?? '')
        : '';

    expect($titleBlock)->toContain('font-size: 0.65rem')
        ->and($titleBlock)->toContain('font-weight: 800')
        ->and($titleBlock)->toContain('color: #fff')
        ->and($titleBlock)->toContain('font-family: var(--dashboard-filter-font)')
        ->and($css)->toContain("--dashboard-filter-font: 'Cairo', Tahoma, sans-serif")
        ->and($layout)->toContain('cairo:400,600,700,800')
        ->and($labelBlock)->toContain('font-size: 0.55rem')
        ->and($selectBlock)->toContain('font-size: 0.6rem')
        ->and($selectBlock)->toContain('color: #000')
        ->and($cardBlock)->toContain('background: rgba(255,255,255,0.08)')
        ->and($cardBlock)->toContain('border: 0.25px solid #f1a257')
        ->and($css)->toContain('width: 4.5cm')
        ->and($css)->toContain('margin-left: 4.5cm')
        ->and($css)->toContain('max-width: calc(100vw - 1.75cm - 4.5cm)');
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

it('shows a large bold dark title for graduate students on the dashboard', function () {
    $home = (string) file_get_contents(resource_path('views/dashboard.blade.php'));
    $css = (string) file_get_contents(public_path('css/dashboard.css'));

    expect($home)->toContain('نظام إدارة الطلبة الخريجون')
        ->and($home)->toContain('app-title-text')
        ->and($home)->toContain('app-title-subtitle')
        ->and($home)->toContain('External Students Management System')
        ->and($home)->not->toContain('نظام إدارة الطلبة الخارجيون')
        ->and($css)->toContain('.app-title-brand .app-title-text')
        ->and($css)->toContain("'Arial Narrow', Arial, sans-serif")
        ->and($css)->toContain('clamp(1.6rem, 3.3vw, 2.4rem)')
        ->and($css)->toContain('font-weight: 700')
        ->and($css)->toContain('color: #111111');

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('نظام إدارة الطلبة الخريجون')
        ->assertSee('External Students Management System')
        ->assertDontSee('نظام إدارة الطلبة الخارجيون');
});

it('places the ministry logos on the dashboard home corners', function () {
    $home = (string) file_get_contents(resource_path('views/dashboard.blade.php'));
    $css = (string) file_get_contents(public_path('css/dashboard.css'));
    $seal = public_path('images/moe-iraq-seal.png');
    $png = (string) file_get_contents($seal);
    $sample = imagecreatefrompng($seal);
    $cornerAlpha = (imagecolorat($sample, 0, 0) >> 24) & 0x7F;
    imagedestroy($sample);
    $endBlock = preg_match('/\.dashboard-moe-badge-end \{[^}]+\}/s', $css, $endMatch)
        ? ($endMatch[0] ?? '')
        : '';
    $startBlock = preg_match('/\.dashboard-moe-badge-start \{[^}]+\}/s', $css, $startMatch)
        ? ($startMatch[0] ?? '')
        : '';

    expect($home)->toContain('dashboard-moe-badge-start')
        ->and($home)->toContain('dashboard-moe-badge-end')
        ->and($home)->toContain('images/moe-vocational-logo.png')
        ->and($home)->toContain('images/moe-iraq-seal.png')
        ->and($startBlock)->toContain('left: 0')
        ->and($endBlock)->toContain('right: 0')
        ->and($css)->toContain('background: transparent')
        ->and(file_exists($seal))->toBeTrue()
        ->and(ord($png[25]))->toBe(6)
        ->and($cornerAlpha)->toBe(127);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('dashboard-moe-badge-end', false)
        ->assertSee('moe-iraq-seal.png', false)
        ->assertSee('وزارة التربية — جمهورية العراق');
});

it('keeps selected sidebar icons inset so they stay visible on the left edge', function () {
    $css = (string) file_get_contents(public_path('css/dashboard.css'));
    $linkBlock = preg_match(
        '/\.dashboard-sidebar \.dashboard-sidebar-link \{[^}]+\}/s',
        $css,
        $matches
    ) ? ($matches[0] ?? '') : '';

    expect($linkBlock)->toContain('width: 1.45cm')
        ->and($css)->toContain('width: 1.75cm')
        ->and($linkBlock)->toContain('overflow: visible')
        ->and($linkBlock)->toContain('box-sizing: border-box')
        ->and($css)->toContain('.dashboard-sidebar {')
        ->and($css)->toContain('overflow: visible');
});
