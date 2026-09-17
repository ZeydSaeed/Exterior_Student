<?php

it('shows the designer footer on the dashboard home page', function () {
    $home = (string) file_get_contents(resource_path('views/dashboard.blade.php'));
    $css = (string) file_get_contents(public_path('css/dashboard.css'));

    $creditPos = strpos($home, 'dashboard-home-footer-credit');
    $whatsappPos = strpos($home, 'dashboard-home-footer-icon-whatsapp');
    $telegramPos = strpos($home, 'dashboard-home-footer-icon-telegram');
    $viberPos = strpos($home, 'dashboard-home-footer-icon-viber');
    $phonePos = strpos($home, 'dashboard-home-footer-phone');
    $emailPos = strpos($home, 'dashboard-home-footer-email');

    expect($home)->toContain('dashboard-home-footer')
        ->and($home)->toContain('Designed &amp; Developed by Dr. Zeyd Saeed')
        ->and($home)->toContain('07805047871')
        ->and($home)->toContain('zeydsaeed@gmail.com')
        ->and($home)->toContain('https://wa.me/9647805047871')
        ->and($home)->toContain('https://t.me/+9647805047871')
        ->and($home)->toContain('viber://chat?number=%2B9647805047871')
        ->and($home)->toContain('mailto:zeydsaeed@gmail.com')
        ->and($home)->toContain('dashboard-home-footer-icon-whatsapp')
        ->and($home)->toContain('dashboard-home-footer-icon-telegram')
        ->and($home)->toContain('dashboard-home-footer-icon-viber')
        ->and($home)->toContain('dashboard-home-footer-icon-email')
        ->and($creditPos)->not->toBeFalse()
        ->and($whatsappPos)->toBeGreaterThan($creditPos)
        ->and($telegramPos)->toBeGreaterThan($whatsappPos)
        ->and($viberPos)->toBeGreaterThan($telegramPos)
        ->and($phonePos)->toBeGreaterThan($viberPos)
        ->and($emailPos)->toBeGreaterThan($phonePos)
        ->and($css)->toContain('.dashboard-home-footer')
        ->and($css)->toContain('background: var(--color-dark-accent)')
        ->and($css)->toContain('flex-direction: row')
        ->and($css)->toContain('justify-content: center')
        ->and($css)->toContain('direction: ltr')
        ->and($css)->toContain('flex-wrap: nowrap')
        ->and($css)->toContain('white-space: nowrap')
        ->and($css)->toContain('padding: 0.5rem 0 0')
        ->and($css)->toContain('gap: 3.75rem')
        ->and($css)->toContain('width: 1.45rem')
        ->and($css)->toContain('.dashboard-home-footer-icon-email')
        ->and($css)->toContain('background: var(--color-light-accent)')
        ->and($css)->toContain('.dashboard-home-footer-icon-whatsapp')
        ->and($css)->toContain('.dashboard-home-footer-icon-telegram')
        ->and($css)->toContain('.dashboard-home-footer-icon-viber');

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Designed & Developed by Dr. Zeyd Saeed')
        ->assertSee('07805047871')
        ->assertSee('zeydsaeed@gmail.com')
        ->assertSee('wa.me/9647805047871', false)
        ->assertSee('mailto:zeydsaeed@gmail.com', false)
        ->assertSee('dashboard-home-footer', false);
});
