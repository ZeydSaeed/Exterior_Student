@echo off
setlocal EnableExtensions

REM Run once on the SERVER PC (not on client laptops).
REM Adds a separate nginx block for LAN clients without changing herd.conf.

set "SERVER_IP=192.168.10.1"
set "APP_HOST=exterior_student.test"
set "SITE_NAME=exterior_student"
set "PROJECT_DIR=%~dp0.."

echo === Exterior Student - Server LAN setup ===
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0configure-server-herd-lan.ps1" -ServerIp "%SERVER_IP%" -AppHost "%APP_HOST%" -SiteName "%SITE_NAME%" -ProjectDir "%PROJECT_DIR%"
if errorlevel 1 (
  echo.
  echo ERROR: LAN setup failed.
  pause
  exit /b 1
)

echo.
pause
endlocal
