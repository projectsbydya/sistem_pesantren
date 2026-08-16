@echo off
chcp 65001 >nul
:: ============================================
:: Setup Local Domains for SaaS Testing
:: Run as Administrator
:: ============================================

echo ============================================
echo SaaS Multi-Tenant Local Domain Setup
echo ============================================
echo.

:: Check admin rights
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Please run as Administrator!
    echo Right-click this file -> Run as Administrator
    pause
    exit /b 1
)

set "HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts"
set "BACKUP_DIR=%USERPROFILE%\hosts-backups"

:: Create backup directory
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

:: Backup current hosts file
echo [1/4] Creating backup of current hosts file...
copy "%HOSTS_FILE%" "%BACKUP_DIR%\hosts-backup-%date:~-4,4%%date:~-10,2%%date:~-7,2%-%time:~0,2%%time:~3,2%%time:~6,2%.txt" >nul
echo      [OK] Backup created

:: Check if entries already exist
echo.
echo [2/4] Checking existing entries...
findstr /C:"alihsan.localhost" "%HOSTS_FILE%" >nul
if %errorLevel% equ 0 (
    echo      [INFO] alihsan.localhost already exists
    goto :SKIP_ADD
)

:: Add new entries
echo.
echo [3/4] Adding SaaS domain entries...
(
    echo.
    echo # ============================================
    echo # SaaS Multi-Tenant Local Development
echo # Added: %date% %time%
echo # ============================================
    echo 127.0.0.1   alihsan.localhost
    echo 127.0.0.1   darulfalah.localhost
    echo 127.0.0.1   tenant1.localhost
    echo 127.0.0.1   tenant2.localhost
    echo 127.0.0.1   tenant3.localhost
    echo 127.0.0.1   demo.localhost
    echo 127.0.0.1   test.localhost
) >> "%HOSTS_FILE%"
echo      [OK] Domains added

:SKIP_ADD
echo.
echo [4/4] Flushing DNS cache...
ipconfig /flushdns >nul
echo      [OK] DNS cache flushed

echo.
echo ============================================
echo Setup Complete!
echo ============================================
echo.
echo Test URLs:
echo   http://alihsan.localhost:8000
:: Perbaikan: escape % dengan %% atau gunakan single line
echo   http://darulfalah.localhost:8000
echo.
echo Next steps:
echo   1. php artisan serve --host=0.0.0.0 --port=8000
echo   2. php artisan db:seed --class=SaaSSeeder
echo   3. Test in browser
echo.
echo To remove these entries, edit:
echo   %HOSTS_FILE%
echo.
pause
