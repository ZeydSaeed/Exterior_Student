@echo off
setlocal EnableExtensions

REM Run this file as Administrator on each client laptop (not on server).

net session >nul 2>&1
if errorlevel 1 (
  echo ERROR: Run as Administrator ^(right-click - Run as administrator^)
  echo.
  pause
  exit /b 1
)

set "SERVER_IP=192.168.10.1"
set "APP_HOST=exterior_student.test"
set "HOSTS=%SystemRoot%\System32\drivers\etc\hosts"

findstr /I /C:"%APP_HOST%" "%HOSTS%" >nul 2>&1
if not errorlevel 1 (
  echo OK: %APP_HOST% already exists in hosts file.
  goto done
)

echo.>>"%HOSTS%"
echo # ExteriorStudent client>>"%HOSTS%"
echo %SERVER_IP%    %APP_HOST%>>"%HOSTS%"
echo OK: Added to hosts: %SERVER_IP%    %APP_HOST%

:done
echo.
pause
endlocal
