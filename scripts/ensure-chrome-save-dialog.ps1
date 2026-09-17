param(
    [Parameter(Mandatory = $true)]
    [string] $ProfileRoot
)

$ErrorActionPreference = 'Stop'

$defaultDir = Join-Path $ProfileRoot 'Default'
$prefsPath = Join-Path $defaultDir 'Preferences'
$utf8 = New-Object System.Text.UTF8Encoding $false

if (-not (Test-Path -LiteralPath $defaultDir)) {
    New-Item -ItemType Directory -Force -Path $defaultDir | Out-Null
}

if (-not (Test-Path -LiteralPath $prefsPath)) {
    [System.IO.File]::WriteAllText($prefsPath, '{"download":{"prompt_for_download":true}}', $utf8)
    exit 0
}

$content = [System.IO.File]::ReadAllText($prefsPath)
$content = [regex]::Replace($content, '"prompt_for_download"\s*:\s*false', '"prompt_for_download":true')

if ($content -notmatch '"prompt_for_download"') {
    $trimmed = $content.TrimStart()
    if ($trimmed.StartsWith('{')) {
        $content = $trimmed.Insert(1, '"download":{"prompt_for_download":true},')
    }
}

[System.IO.File]::WriteAllText($prefsPath, $content, $utf8)
