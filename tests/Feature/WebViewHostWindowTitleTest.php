<?php

it('centers the desktop caption title and pins the icon to the left', function () {
    $source = (string) file_get_contents(base_path('scripts/webview-host/Program.cs'));

    expect($source)->toContain('نظام إدارة الطلبة الخريجون - الإصدار 1')
        ->and($source)->toContain('ContentAlignment.MiddleCenter')
        ->and($source)->toContain('AnchorStyles.Top | AnchorStyles.Left')
        ->and($source)->toContain('ContentHost')
        ->and($source)->toContain('form.ContentHost.Controls.Add(web)')
        ->and($source)->toContain('إغلاق')
        ->and($source)->toContain('تكبير')
        ->and($source)->toContain('تصغير')
        ->and($source)->toContain('استعادة')
        ->and($source)->not->toContain('نظام إدارة الطلبة الخارجيين');
});

it('uses the same release title in the browser window caption', function () {
    $layout = (string) file_get_contents(resource_path('views/layouts/dashboard.blade.php'));
    $login = (string) file_get_contents(resource_path('views/auth/login.blade.php'));

    expect(config('app.window_title'))->toBe('نظام إدارة الطلبة الخريجون - الإصدار 1')
        ->and($layout)->toContain("config('app.window_title')")
        ->and($login)->toContain("config('app.window_title')");

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('<title>'.config('app.window_title').'</title>', false);
});
