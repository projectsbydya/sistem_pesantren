@echo off
REM Windows batch script untuk menjalankan tenant tests

echo ==========================================
echo   TENANT TESTING SUITE
echo ==========================================
echo.

REM Jalankan semua test tenant
echo [1/4] Menjalankan semua test tenant...
php artisan test --filter=Tenant --colors=never

echo.
echo [2/4] Test Coverage Report...
php artisan test --filter=Tenant --coverage --min=80 --colors=never 2>nul || echo Coverage test skipped (xdebug not installed)

echo.
echo [3/4] Running TenantScope Tests...
php artisan test --filter=TenantScopeTest --colors=never

echo.
echo [4/4] Running TenantService Tests...
php artisan test --filter=TenantServiceTest --colors=never

echo.
echo ==========================================
echo   TESTING COMPLETE
echo ==========================================
pause
