param(
    [string]$ServerIp = '192.168.10.1',
    [string]$AppHost = 'exterior_student.test',
    [string]$SiteName = 'exterior_student',
    [string]$ProjectDir = ''
)

$ErrorActionPreference = 'Stop'

$herdHome = Join-Path $env:USERPROFILE '.config\herd'
$herdBat = Join-Path $herdHome 'bin\herd.bat'
$herdConf = Join-Path $herdHome 'config\nginx\herd.conf'
$lanConf = Join-Path $herdHome 'config\pro\nginx\exterior-student-lan.conf'
$backup = "$herdConf.lan-backup"
$sitesLink = Join-Path $herdHome "config\valet\Sites\$SiteName"

if (-not (Test-Path $herdBat)) {
    throw "Herd not found: $herdBat"
}

if (-not (Test-Path $herdConf)) {
    throw "Herd nginx config not found: $herdConf"
}

if ($ProjectDir -ne '' -and -not (Test-Path $sitesLink)) {
    Write-Host "Linking site in Herd..."
    Push-Location $ProjectDir
    & $herdBat link $SiteName | Out-Host
    Pop-Location
}

$text = [IO.File]::ReadAllText($herdConf)

if ($text -match 'listen\s+80\s+default_server' -and $text -notmatch 'listen\s+127\.0\.0\.1:80') {
    if (Test-Path $backup) {
        [IO.File]::Copy($backup, $herdConf, $true)
        $text = [IO.File]::ReadAllText($herdConf)
        Write-Host 'OK: Restored herd.conf (local app access fixed).'
    }
}

if (-not (Test-Path $backup)) {
    [IO.File]::Copy($herdConf, $backup, $true)
    Write-Host 'OK: Backup saved to herd.conf.lan-backup'
}

$internalMatch = [regex]::Match($text, 'location ~\* /([^/]+)/\(\[A-Z\]\+\:\)\(\.\*\) \{[\s\S]*?\}')
if (-not $internalMatch.Success) {
    throw 'Could not read internal location block from herd.conf'
}

$internalBlock = $internalMatch.Value
$herdHomeUnix = $herdHome.Replace('\', '/')

$lan = @"
server {
    # Listen on all interfaces so nginx still starts if LAN IP is assigned later.
    # Clients reach the server via hosts -> 192.168.10.1 after ensure-server-lan-ip.ps1.
    listen 80;
    server_name ${AppHost};
    root /;
    charset utf-8;
    client_max_body_size 128M;

    $internalBlock

    location / {
        rewrite ^ "C:/Program Files/Herd/resources/app.asar.unpacked/resources/valet/server.php" last;
    }

    access_log off;
    error_log '$herdHomeUnix/Log/nginx-error.log';

    error_page 404 "C:/Program Files/Herd/resources/app.asar.unpacked/resources/valet/server.php";

    location ~ [^/]\.php(/|$) {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass `$herd_sock;
        fastcgi_index "C:/Program Files/Herd/resources/app.asar.unpacked/resources/valet/server.php";
        fastcgi_param QUERY_STRING  `$query_string;
        fastcgi_param REQUEST_METHOD  `$request_method;
        fastcgi_param CONTENT_TYPE  `$content_type;
        fastcgi_param CONTENT_LENGTH  `$content_length;
        fastcgi_param SCRIPT_FILENAME  `$request_filename;
        fastcgi_param SCRIPT_NAME  `$fastcgi_script_name;
        fastcgi_param REQUEST_URI  `$request_uri;
        fastcgi_param DOCUMENT_URI  `$document_uri;
        fastcgi_param DOCUMENT_ROOT  `$document_root;
        fastcgi_param SERVER_PROTOCOL  `$server_protocol;
        fastcgi_param GATEWAY_INTERFACE CGI/1.1;
        fastcgi_param SERVER_SOFTWARE  nginx/`$nginx_version;
        fastcgi_param REMOTE_ADDR  `$remote_addr;
        fastcgi_param REMOTE_PORT  `$remote_port;
        fastcgi_param SERVER_ADDR  `$server_addr;
        fastcgi_param SERVER_PORT  `$server_port;
        fastcgi_param SERVER_NAME  `$server_name;
        fastcgi_param HTTPS   `$https if_not_empty;
        fastcgi_param HERD_HOME "$herdHomeUnix";
        fastcgi_param REDIRECT_STATUS  200;
        fastcgi_param HTTP_PROXY  "";
        fastcgi_buffer_size 512k;
        fastcgi_buffers 16 512k;
        fastcgi_param SCRIPT_FILENAME "C:/Program Files/Herd/resources/app.asar.unpacked/resources/valet/server.php";
        fastcgi_param PATH_INFO `$fastcgi_path_info;
    }

    location ~ /\.ht {
        deny all;
    }
}
"@

$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[IO.File]::WriteAllText($lanConf, $lan, $utf8NoBom)
# If a previous local launch disabled LAN config, restore the active filename
$lanDisabled = "$lanConf.disabled"
if (Test-Path $lanDisabled) {
    Remove-Item $lanDisabled -Force -ErrorAction SilentlyContinue
}
Write-Host "OK: Created LAN nginx config for $AppHost on $ServerIp."

# Ensure server Ethernet has the LAN static IP (required for client laptops)
$ensureIp = Join-Path $PSScriptRoot 'ensure-server-lan-ip.ps1'
if (Test-Path $ensureIp) {
    Write-Host 'Ensuring server LAN IP...'
    & powershell -NoProfile -ExecutionPolicy Bypass -File $ensureIp -ServerIp $ServerIp
    if ($LASTEXITCODE -eq 3) {
        Write-Host 'WARN: Run this script as Administrator to assign the LAN IP automatically.'
    }
}

& $herdBat restart | Out-Host
Write-Host 'OK: Herd restarted.'

try {
    taskkill /IM httpd.exe /F 2>$null | Out-Null
    Write-Host 'OK: Apache stopped.'
} catch {
}

if (-not (Get-NetFirewallRule -DisplayName 'Exterior Student HTTP' -ErrorAction SilentlyContinue)) {
    New-NetFirewallRule -DisplayName 'Exterior Student HTTP' -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow | Out-Null
    Write-Host 'OK: Firewall rule added.'
} else {
    Write-Host 'OK: Firewall rule already exists.'
}

Write-Host ''
Write-Host "Done. Server local:  http://$AppHost"
Write-Host "Client laptops:      http://$AppHost  (after install-client-hosts.bat)"
