@echo off
setlocal EnableExtensions
echo === Exterior Student - Client network test ===
echo.

set "SERVER_IP=192.168.10.1"
set "APP_HOST=exterior_student.test"

echo [1] Ping server %SERVER_IP%
ping -n 2 %SERVER_IP%
echo.

echo [2] hosts entry for %APP_HOST%
findstr /I /C:"%APP_HOST%" "%SystemRoot%\System32\drivers\etc\hosts"
echo.

echo [3] HTTP test http://%APP_HOST%
powershell -NoProfile -Command "try { $r=Invoke-WebRequest -Uri 'http://%APP_HOST%/' -UseBasicParsing -TimeoutSec 5; if ($r.Content -match 'XAMPP|Welcome to XAMPP') { Write-Host 'FAIL: XAMPP page (run configure-server-herd-lan.bat on server)' } else { Write-Host 'OK HTTP' $r.StatusCode } } catch { Write-Host 'FAIL HTTP:' $_.Exception.Message }"
echo.

echo [4] This PC IPv4 (look for 192.168.10.x)
ipconfig | findstr /I "IPv4 Ethernet"
echo.
pause
