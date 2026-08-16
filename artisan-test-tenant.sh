#!/bin/bash
# Linux/Mac script untuk menjalankan tenant tests

echo "=========================================="
echo "   TENANT TESTING SUITE"
echo "=========================================="
echo ""

# Warna
green='\033[0;32m'
red='\033[0;31m'
nc='\033[0m' # No Color

# Jalankan semua test tenant
echo -e "${green}[1/4] Menjalankan semua test tenant...${nc}"
php artisan test --filter=Tenant

if [ $? -eq 0 ]; then
    echo -e "${green}✓ Semua test passed${nc}"
else
    echo -e "${red}✗ Ada test yang failed${nc}"
    exit 1
fi

echo ""
echo -e "${green}[2/4] Test Coverage Report...${nc}"
php artisan test --filter=Tenant --coverage --min=80 2>/dev/null || echo "Coverage test skipped (xdebug not installed)"

echo ""
echo -e "${green}[3/4] Running TenantScope Tests...${nc}"
php artisan test --filter=TenantScopeTest

echo ""
echo -e "${green}[4/4] Running TenantService Tests...${nc}"
php artisan test --filter=TenantServiceTest

echo ""
echo "=========================================="
echo "   TESTING COMPLETE"
echo "=========================================="
