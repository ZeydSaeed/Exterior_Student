<?php

it('keeps the LAN switch settings in the server launcher', function () {
    $script = (string) file_get_contents(base_path('scripts/silent-start-app.vbs'));

    expect($script)->toContain('Const SERVER_IP = "192.168.10.1"')
        ->and($script)->toContain('Const APP_HOST = "exterior_student.test"')
        ->and($script)->toContain('lanConf = herdHome & "\config\pro\nginx\exterior-student-lan.conf"')
        ->and($script)->toContain('EnsureLanConfigEnabled')
        ->and($script)->toContain('EnsureServerLanIp')
        ->and($script)->toContain('EnsureFirewallHttp')
        ->and($script)->toContain('configure-server-herd-lan.bat');
});

it('opens chrome immediately when the site is already up without restarting nginx', function () {
    $script = (string) file_get_contents(base_path('scripts/silent-start-app.vbs'));
    $fastPath = preg_match(
        '/If ProcessExists\("mysqld.exe"\) Then.*?If SiteReachable\(appUrl\) Then.*?OpenChromeApp.*?WScript\.Quit 0/s',
        $script
    );

    expect($fastPath)->toBe(1)
        ->and($script)->toContain("' Keep LAN nginx config and server IP intact. Never restart nginx while the site is already serving clients.")
        ->and($script)->toContain('StartNginxExplicit');
});

it('keeps the client laptop LAN host and server IP unchanged', function () {
    $script = (string) file_get_contents(base_path('scripts/silent-start-client.vbs'));

    expect($script)->toContain('Const SERVER_IP = "192.168.10.1"')
        ->and($script)->toContain('Const APP_HOST = "exterior_student.test"')
        ->and($script)->toContain('install-client-hosts.bat')
        ->and($script)->toContain('HostsPointsToServer');
});

it('does not wait a fixed five seconds when the local app is already reachable', function () {
    $script = (string) file_get_contents(base_path('ExteriorStudent-Start.vbs'));

    expect($script)->not->toContain('WScript.Sleep 5000')
        ->and($script)->toContain('SiteReachable(appUrl)')
        ->and($script)->toContain('appUrl = "http://exterior_student.test"');
});
