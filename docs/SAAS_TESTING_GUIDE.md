# SaaS Testing Guide - Multi-Tenant dengan Subdomain

## 📋 Daftar Test

### 1. Unit Test (Model & Service)
```bash
php artisan test --filter=Unit
```

### 2. Feature Test (HTTP)
```bash
# Semua feature test
php artisan test --filter=Feature

# Spesifik SaaS
php artisan test --filter=SaaS
```

### 3. Integration Test
```bash
php artisan test --filter=Integration
```

---

## 🧪 Test Categories

### A. Tenant Resolution Test
```bash
php artisan test --filter=SaaSTenantResolutionTest
```

**Apa yang di-test:**
- ✅ Subdomain resolution (`demo1.localhost`)
- ✅ Custom domain (`demo1.sch.id`)
- ✅ Header injection (`X-Tenant-ID`)
- ✅ Session-based tenant
- ✅ Invalid subdomain returns 401

### B. Cross-Tenant Isolation Test
```bash
php artisan test --filter=SaaSMultiTenantTest
```

**Apa yang di-test:**
- ✅ Admin hanya lihat data tenant sendiri
- ✅ Parent hanya lihat anak sendiri
- ✅ Santri hanya lihat data diri
- ✅ Super admin lihat semua
- ✅ Tidak ada data leak antar tenant

### C. Registration Flow Test
```bash
php artisan test --filter=SaaSRegistrationFlowTest
```

**Apa yang di-test:**
- ✅ Pesantren reg → auto create tenant + admin
- ✅ Santri reg → validasi NIS
- ✅ Orang tua reg → validasi minimal 1 anak
- ✅ Slug unik generation
- ✅ Trial period 30 hari

---

## 🌐 Testing Subdomain via DNS

### Metode 1: Local Hosts File

Edit file hosts:

**Windows:** `C:\Windows\System32\drivers\etc\hosts`
**Mac/Linux:** `/etc/hosts`

```
# SaaS Local Development
127.0.0.1   localhost
127.0.0.1   demo1.localhost
127.0.0.1   demo2.localhost
127.0.0.1   tenant1.localhost
127.0.0.1   tenant2.localhost
127.0.0.1   demo1.test
127.0.0.1   demo2.test
```

### Metode 2: Laravel Valet (Mac/Linux)
```bash
# Valet automatically handles *.test domains
valet link sistem-pesantren

# Access via:
# http://sistem-pesantren.test
# http://demo1.sistem-pesantren.test
```

### Metode 3: Valet Windows / Laragon
```bash
# Laragon: Auto virtual hosts
# Set project root di K:\Projects\sistem_pesantren

# Akses:
# http://sistem-pesantren.test
# http://demo1.sistem-pesantren.test
```

### Metode 4: Serveo / ngrok (Public Testing)
```bash
# ngrok dengan subdomain
ngrok http --subdomain=demo1 8000

# Hasil:
# https://demo1.ngrok.io
```

---

## 🔧 Setup Test Database

```bash
# .env.testing
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Atau MySQL test database
DB_DATABASE=sistem_pesantren_test
```

```bash
# Fresh migrate untuk test
php artisan migrate:fresh --env=testing

# Seed dengan data test
php artisan db:seed --class=SaaSSeeder --env=testing
```

---

## 🎯 Test Scenarios Checklist

### ✅ Tenant Resolution
- [ ] `GET http://demo1.localhost/test/tenant` → Tenant A
- [ ] `GET http://demo2.localhost/test/tenant` → Tenant B
- [ ] `GET http://invalid.localhost/test/tenant` → 401
- [ ] `GET /test/tenant` with `X-Tenant-ID: 1` → Tenant 1

### ✅ Role Access
- [ ] Admin akses `/dashboard/santri` → All santri in tenant
- [ ] Parent akses `/dashboard/santri` → Only own children
- [ ] Santri akses `/dashboard/santri` → Only self
- [ ] Super admin `/dashboard/super-admin` → All tenants

### ✅ Cross-Tenant Protection
- [ ] Admin Tenant A akses API Tenant B → 403/404
- [ ] Parent Tenant A akses santri Tenant B → 403
- [ ] URL manipulation `/santri/{id}` cross-tenant → 404

### ✅ Registration
- [ ] Register pesantren → Tenant + Admin created
- [ ] Register santri dengan NIS valid → Success
- [ ] Register santri dengan NIS invalid → Error
- [ ] Register orang tua dengan 2 anak → Success
- [ ] Register orang tua tanpa anak → Error

---

## 🛠️ Testing Tools

### PHPUnit Commands
```bash
# Run specific test
php artisan test tests/Feature/SaaSTenantResolutionTest.php

# With filter
php artisan test --filter=test_tenant_resolved_from_subdomain

# Parallel testing
php artisan test --parallel

# Coverage report
php artisan test --coverage
```

### HTTP Testing
```bash
# Using curl
curl -H "Accept: application/json" \
     -H "X-Tenant-ID: 1" \
     http://localhost:8000/api/santri

# Using HTTPie
http :8000/api/santri X-Tenant-ID:1 Accept:application/json
```

### Browser Testing (Pest/Playwright)
```bash
# Dusk (Laravel browser testing)
php artisan dusk

# Playwright
npx playwright test
```

---

## 📝 Sample Test Cases

### Test Tenant Resolution Priority
```php
public function test_tenant_resolution_priority(): void
{
    // Priority: Header > Subdomain > Session
    
    $tenantA = Tenant::factory()->create(['slug' => 'a']);
    $tenantB = Tenant::factory()->create(['slug' => 'b']);
    
    // Header should win over subdomain
    $response = $this->get(
        'http://a.localhost/test/tenant',
        [
            'Accept' => 'application/json',
            'X-Tenant-ID' => $tenantB->id,
        ]
    );
    
    $response->assertJson(['tenant_id' => $tenantB->id]);
}
```

### Test Subdomain to Tenant Mapping
```php
public function test_subdomain_tenant_mapping(): void
{
    $mappings = [
        'demo1.localhost' => 'Demo Pesantren 1',
        'demo2.localhost' => 'Demo Pesantren 2',
        'custom.sch.id' => 'Custom Domain Tenant',
    ];
    
    foreach ($mappings as $host => $name) {
        $response = $this->get("http://{$host}/test/tenant");
        $response->assertJson(['tenant_name' => $name]);
    }
}
```

---

## 🚨 Common Testing Issues

### Issue 1: `hosts` file not working
**Fix:** Flush DNS cache
```bash
# Windows
ipconfig /flushdns

# Mac
sudo killall -HUP mDNSResponder

# Linux
sudo systemctl restart NetworkManager
```

### Issue 2: Permission denied hosts file
**Fix:** Run as administrator or use sudo

### Issue 3: Subdomain resolves to real website
**Fix:** Pastikan entry di hosts file tidak dikomentari
```
# ❌ Salah (dikomentari)
# 127.0.0.1 demo1.localhost

# ✅ Benar
127.0.0.1 demo1.localhost
```

### Issue 4: Test database not isolated
**Fix:** Pastikan menggunakan RefreshDatabase trait
```php
use RefreshDatabase;
```

---

## 🎓 Best Practices

1. **Always use RefreshDatabase** untuk test isolation
2. **Test dengan SQLite in-memory** untuk kecepatan
3. **Mock external services** (email, SMS)
4. **Test edge cases:**
   - Tenant inactive
   - Trial expired
   - User suspended
   - Cross-tenant access attempt

5. **Run test suite sebelum deploy:**
   ```bash
   php artisan test --parallel --coverage
   ```

---

## 🔗 Resources

- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [PHPUnit Docs](https://phpunit.de/)
- [Pest PHP](https://pestphp.com/)
- [Laravel Dusk](https://laravel.com/docs/dusk)
