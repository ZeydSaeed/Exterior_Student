<?php

it('adds a password visibility toggle next to new password fields on the accounts page', function () {
    $html = file_get_contents(resource_path('views/accounts/index.blade.php'));

    expect($html)->toContain('accounts-password-toggle')
        ->and($html)->toContain('accounts-password-input')
        ->and($html)->toContain('إظهار كلمة المرور')
        ->and($html)->toContain('accounts-password-icon-hidden')
        ->and($html)->toContain('accounts-password-icon-visible');
});
