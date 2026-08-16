@echo off
chcp 65001 >nul
:: ============================================
:: Test Tenant Resolution via Subdomain
:: ============================================

echo ============================================
echo SaaS Tenant Resolution Test
echo ============================================
echo.

:: Colors
set "GREEN=[92m"
set "RED=[91m"
set "YELLOW=[93m"
set "NC=[0m"

set "BASE_URL=http://localhost:8000"
set "SUBDOMAIN_ALIHSAN=http://alihsan.localhost:8000"
set "SUBDOMAIN_DARUL=http://darulfalah.localhost:8000"

:: Check if Laravel is running
echo [TEST] Checking if Laravel server is running...
curl -s -o nul -w "%%{http_code}" %BASE_URL% > temp.txt
set /p STATUS=<temp.txt
del temp.txt

if "%STATUS%"=="000" (
    echo %RED%[FAIL]%NC% Laravel server not running
echo.
    echo Start with: php artisan serve --host=0.0.0.0 --port=8000
echo.
    pause
    exit /b 1
) else (
    echo %GREEN%[PASS]%NC% Laravel running (HTTP %STATUS%)
)

echo.
echo [TEST] Testing tenant resolution via header...
curl -s -H "X-Tenant-ID: 1" -H "Accept: application/json" "%BASE_URL%/api/santri" > temp.json 2>nul
findstr /C:"tenant_id" temp.json >nul
if %errorLevel% equ 0 (
    echo %GREEN%[PASS]%NC% Header resolution working
echo      Response: 
cat temp.json
del temp.json 2>nul
) else (
    echo %YELLOW%[INFO]%NC% Header test: Check API route exists
)

echo.
echo [TEST] Checking subdomain DNS resolution...
nslookup alihsan.localhost > nul 2>&1
if %errorLevel% equ 0 (
    echo %GREEN%[PASS]%NC% alihsan.localhost resolves correctly
) else (
    echo %RED%[FAIL]%NC% alihsan.localhost not in hosts file
echo      Run: setup-local-domains.bat as Administrator
)

echo.
echo [TEST] Checking subdomain 2 DNS resolution...
nslookup darulfalah.localhost > nul 2>&1
if %errorLevel% equ 0 (
    echo %GREEN%[PASS]%NC% darulfalah.localhost resolves correctly
) else (
    echo %RED%[FAIL]%NC% darulfalah.localhost not in hosts file
echo      Run: setup-local-domains.bat as Administrator
)

echo.
echo ============================================
echo Test Summary
echo ============================================
echo.
echo To run PHPUnit tests:
echo   php artisan test --filter=SaaSTenantResolutionTest
echo.
echo To test manually:
echo   curl http://alihsan.localhost:8000/test/tenant
echo   curl -H "X-Tenant-ID: 1" http://localhost:8000/api/santri
echo.

pause
