param(
    [string]$ServerIp = '192.168.10.1',
    [int]$PrefixLength = 24
)

$ErrorActionPreference = 'Stop'

function Test-IsAdmin {
    $id = [Security.Principal.WindowsIdentity]::GetCurrent()
    $p = New-Object Security.Principal.WindowsPrincipal($id)
    return $p.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

$existing = Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue |
    Where-Object { $_.IPAddress -eq $ServerIp }

if ($existing) {
    Write-Host "OK: Server IP $ServerIp already present on '$($existing.InterfaceAlias)'."
    exit 0
}

# Prefer a connected Ethernet adapter that is not Wi-Fi/Bluetooth/Loopback
$candidates = Get-NetAdapter -ErrorAction SilentlyContinue |
    Where-Object {
        $_.Status -eq 'Up' -and
        $_.Name -notmatch 'Wi-?Fi|Wireless|Bluetooth|Loopback|vEthernet|Virtual' -and
        ($_.Name -match 'Ethernet|Local Area|LAN|QTS|USB')
    }

if (-not $candidates) {
    # Fallback: any Up non-WiFi physical-looking adapter
    $candidates = Get-NetAdapter -ErrorAction SilentlyContinue |
        Where-Object {
            $_.Status -eq 'Up' -and
            $_.Name -notmatch 'Wi-?Fi|Wireless|Bluetooth|Loopback|vEthernet|Virtual'
        }
}

if (-not $candidates) {
    Write-Host "WARN: No suitable Ethernet adapter found (Status=Up)."
    Write-Host "Connect the switch cable to the server Ethernet adapter, then retry."
    exit 2
}

$adapter = $candidates | Select-Object -First 1
Write-Host "Using adapter: $($adapter.Name) ($($adapter.InterfaceDescription))"

if (-not (Test-IsAdmin)) {
    Write-Host "ERROR: Administrator rights required to set static IP $ServerIp on '$($adapter.Name)'."
    exit 3
}

# Remove any previous DHCP IPv4 on this adapter that would conflict, keep other interfaces intact
$ipv4 = Get-NetIPAddress -InterfaceIndex $adapter.ifIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue |
    Where-Object { $_.IPAddress -ne '127.0.0.1' -and $_.PrefixOrigin -ne 'WellKnown' }

foreach ($addr in $ipv4) {
    if ($addr.IPAddress -ne $ServerIp) {
        Write-Host "Removing old IPv4 $($addr.IPAddress) from '$($adapter.Name)'..."
        Remove-NetIPAddress -InterfaceIndex $adapter.ifIndex -IPAddress $addr.IPAddress -Confirm:$false -ErrorAction SilentlyContinue
        Remove-NetRoute -InterfaceIndex $adapter.ifIndex -DestinationPrefix "$($addr.IPAddress)/$($addr.PrefixLength)" -Confirm:$false -ErrorAction SilentlyContinue
    }
}

try {
    New-NetIPAddress -InterfaceIndex $adapter.ifIndex -IPAddress $ServerIp -PrefixLength $PrefixLength -ErrorAction Stop | Out-Null
} catch {
    # If address object exists half-configured, try set
    Set-NetIPAddress -InterfaceIndex $adapter.ifIndex -IPAddress $ServerIp -PrefixLength $PrefixLength -ErrorAction Stop
}

# Ensure no default gateway is forced on this LAN NIC (isolated switch)
Get-NetRoute -InterfaceIndex $adapter.ifIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue |
    Where-Object { $_.DestinationPrefix -eq '0.0.0.0/0' } |
    ForEach-Object {
        Remove-NetRoute -InterfaceIndex $_.InterfaceIndex -DestinationPrefix $_.DestinationPrefix -Confirm:$false -ErrorAction SilentlyContinue
    }

$check = Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue |
    Where-Object { $_.IPAddress -eq $ServerIp }

if (-not $check) {
    throw "Failed to assign $ServerIp to '$($adapter.Name)'."
}

Write-Host "OK: Assigned $ServerIp/$PrefixLength to '$($adapter.Name)'."
exit 0
