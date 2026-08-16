# Sistem Pesantren - Multi-Tenant SaaS

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-blue?style=flat&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange?style=flat&logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Sistem Manajemen Pesantren berbasis **Multi-Tenant SaaS** yang mendukung pengelolaan multiple pondok pesantren dalam satu platform dengan isolasi data yang ketat. Dirancang dengan arsitektur berlapis: **Platform Layer**, **Academic Core**, **Pesantren Core**, dan **Feature Packs** per program.

---

## System Architecture

Sistem ini terdiri dari **3 Core Layer** yang bersifat universal (berlaku untuk semua tenant) dan **Feature Packs** yang bersifat program-specific.

```
┌─────────────────────────────────────────────────────────────┐
│                    FEATURE PACKS (per program)              │
│  Diniyah ✅ │ Modern ▶️ │ Salafiyah ⏳ │ Tahfidz ⏳ │ ...  │
├─────────────────────────────────────────────────────────────┤
│              PESANTREN CORE (Universal) ✅ FROZEN           │
│  Kamar · PenempatanKamar · MutasiKamar · Pelanggaran       │
│  Sanksi · Perizinan · MonitoringKarakter · KegiatanHarian   │
├─────────────────────────────────────────────────────────────┤
│              ACADEMIC CORE (Universal) ✅ FROZEN            │
│  Program · Kelas · Subject · Jadwal · Ustadz · Santri      │
│  Parent · UstadzKelas · Absensi · Nilai · Materi           │
│  Elearning · Raport                                         │
├─────────────────────────────────────────────────────────────┤
│              PLATFORM LAYER (Universal) ✅ FROZEN           │
│  Multi-Tenant · Auth · Role & Permission · Provisioning     │
│  Dashboard · Navigation · Sidebar · Settings                │
│  Tenant Isolation · Feature Management                      │
└─────────────────────────────────────────────────────────────┘
```

### 1. Platform Layer (Frozen)

Infrastruktur universal untuk semua tenant.

- **Multi-Tenant (Subdomain)** — Setiap pesantren di-resolve via subdomain (`alhikmah.pesantren.id`)
- **Authentication** — Login per subdomain dengan session domain wildcard
- **Role & Permission** — Spatie Permission dengan policy-based authorization (fail-closed)
- **Provisioning** — Auto-create user account saat registrasi santri/ustadz
- **Dashboard** — Role-branched: Admin, Ustadz, Santri, Parent
- **Navigation & Sidebar** — Dynamic menu via `NavigationGateService`
- **Tenant Isolation** — `HasTenant` trait + `TenantScope` global scope

### 2. Academic Core (Frozen)

Engine akademik universal. Berlaku untuk semua program.

| Entity | Deskripsi |
|--------|-----------|
| **Program** | Jenis program pembelajaran (Diniyah, Formal, Modern, dll.) |
| **Kelas** | Kelas per program |
| **Subject** | Mata pelajaran per program |
| **Jadwal** | Jadwal harian/mingguan per kelas |
| **Ustadz** | Data ustadz/pengajar |
| **Santri** | Data santri lengkap (NIS, kelas, kamar, mondok) |
| **Parent** | Data orang tua/wali |
| **UstadzKelas** | Assignment ustadz ke kelas + mata pelajaran |
| **AbsensiSantri** | Absensi santri per jadwal |
| **AbsensiUstadz** | Absensi ustadz per jadwal |
| **Nilai** | Nilai akademik per mata pelajaran |
| **Materi** | Materi pembelajaran per kelas |
| **Elearning** | Konten e-learning |
| **Raport** | Raport elektronik santri |

### 3. Pesantren Core (Frozen)

Engine kepesantrenan universal. **Bukan program pack** — berlaku untuk semua tenant.

| Entity | Deskripsi |
|--------|-----------|
| **Kamar** | Manajemen kamar asrama |
| **PenempatanKamar** | Penempatan santri ke kamar |
| **MutasiKamar** | Mutasi/perpindahan kamar |
| **Pelanggaran** | Pencatatan pelanggaran santri |
| **Sanksi** | Manajemen sanksi terkait pelanggaran |
| **Perizinan** | Permohonan izin dengan flow approval |
| **MonitoringKarakter** | Penilaian karakter santri |
| **KegiatanHarian** | Monitoring kegiatan harian santri |

### 4. Program Registry & Feature Packs

Saat onboarding, tenant memilih program dari **6 program aktif**:

| # | Program | Status Pack |
|---|---------|-------------|
| 1 | Diniyah | ✅ Frozen |
| 2 | Formal | ⏳ Planned |
| 3 | Modern | ▶️ In Progress |
| 4 | Salafiyah | ⏳ Planned |
| 5 | Pesantren Quran & Tahfidz | ⏳ Planned |
| 6 | Terpadu | ⏳ Planned |

> **Note**: Program "Pesantren" sudah dipindahkan ke **Pesantren Core** dan bukan lagi program onboarding. Fitur kepesantrenan (kamar, pelanggaran, dll.) otomatis tersedia untuk semua tenant.

#### Diniyah Pack (Frozen)

| Entity | Type |
|--------|------|
| DiniyahHafalan | doa, hadits, surat |
| DiniyahMonitoring | sholat, adab, akhlak |
| DiniyahAssessment | keagamaan, akhlak |

Architecture: `DiniyahController`, `DiniyahService`, 3 Models, 3 Policies.

---

## Role-Based Access Control (RBAC)

| Role | Akses |
|------|-------|
| **Super Admin** | Manage semua tenant, NO access ke data tenant |
| **Tenant Admin** | Full access ke tenant sendiri |
| **Bendahara** | Manage keuangan, bills, tabungan |
| **Ustadz** | Manage kelas, nilai, absensi, jadwal |
| **Parent** | View data santri sendiri only |
| **Santri** | View profil & nilai sendiri only |

**Implementation**: Spatie Laravel Permission + Policy-based authorization (fail-closed)

---

## Security

- **TenantScope** — Global query scope dengan fail-closed design
- **Policy Authorization** — All actions validated via Laravel Policies
- **Super Admin Isolation** — Super admin blocked dari data tenant
- **Cross-Tenant Protection** — 403/404 untuk unauthorized cross-tenant access
- **State Isolation** — Per-request tenant context (no shared state)

---

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/Dashboard/    # Tenant-scoped controllers
│   ├── Middleware/
│   │   ├── ResolveTenant.php     # Tenant resolution dari subdomain/domain
│   │   └── EnsureOwnsTenant.php  # Verifikasi user belongs to tenant
│   └── Requests/                 # Form requests dengan tenant validation
├── Models/
│   ├── Traits/HasTenant.php      # Auto-apply TenantScope
│   ├── Scopes/TenantScope.php    # Fail-closed query scope
│   └── [Entity].php              # Santri, Ustadz, Kamar, Pelanggaran, dll.
├── Policies/                     # Authorization policies (fail-closed)
├── Services/
│   ├── TenantService.php         # Tenant context management
│   ├── TenantSetupService.php    # Onboarding & setup
│   ├── NavigationGateService.php # Sidebar visibility gates
│   └── [Entity]Service.php       # Business logic per entity
├── Helpers/TenantHelper.php      # Global helper functions
└── Providers/

config/
└── academic_programs.php         # Feature definitions per program

database/
├── migrations/
├── seeders/
│   ├── ProgramSeeder.php         # 7 programs (6 active + pesantren inactive)
│   ├── RolePermissionSeeder.php
│   └── DemoDataSeeder.php
└── factories/

routes/
├── web.php                       # Tenant routes dengan middleware
└── super-admin.php               # Super admin routes (no tenant)
```

## Requirements

- **PHP**: 8.3+
- **Database**: MySQL 8.0+ atau MariaDB 10.5+
- **Web Server**: Apache/Nginx dengan mod_rewrite
- **Extensions**: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## Quick Start

### 1. Clone & Install

```bash
git clone https://github.com/username/sistem_pesantren.git
cd sistem_pesantren
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

### 2. Environment Configuration

```bash
# .env
APP_NAME="Sistem Pesantren"
APP_DOMAIN=pesantren.id          # Domain utama untuk subdomain resolution
APP_URL=http://pesantren.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_pesantren
DB_USERNAME=root
DB_PASSWORD=

# Wildcard DNS untuk multi-tenancy
# *.pesantren.id -> A record ke server IP
```

### 3. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE sistem_pesantren CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations & seeders
php artisan migrate:fresh --seed
```

### 4. Configure Web Server

#### Nginx (recommended)

```nginx
server {
    listen 80;
    server_name *.pesantren.id pesantren.id;
    root /var/www/sistem_pesantren/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        # ... standard php-fpm config
    }
}
```

#### DNS Configuration

```
# Wildcard A record
*.pesantren.id    A    YOUR_SERVER_IP
pesantren.id      A    YOUR_SERVER_IP
```

### 5. Create First Tenant

```bash
php artisan tinker

>>> $tenant = \App\Models\Tenant::create([
...     "name" => "Pesantren Al-Hikmah",
...     "slug" => "alhikmah",
...     "domain" => "alhikmah.pesantren.id",
...     "is_active" => true
... ]);
>>> exit
```

Access: `http://alhikmah.pesantren.id`

## Testing

> **Note:** the `tests/` directory is intentionally excluded from this
> repository via `.gitignore` and is not shipped here. Run tests from a
> local/CI checkout that still has the test suite.

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter=TenantScopeTest
php artisan test --filter=SaaSMultiTenantTest
php artisan test --filter=AccessControlTest

# Test coverage (requires Xdebug)
php artisan test --coverage
```

## Documentation

- [Multi-Tenant Setup Guide](TENANT_SETUP.md) - Detailed tenant configuration
- [Testing Guide](docs/SAAS_TESTING_GUIDE.md) - Multi-tenant testing strategies
- [Production Checklist](docs/PRODUCTION_CHECKLIST.md) - Production deployment checklist

## Security Best Practices

1. **Never share database credentials** across tenants
2. **Always validate tenant_id** in custom queries (gunakan TenantScope)
3. **Block super admin** dari data tenant via policies
4. **Use HTTPS** untuk production dengan wildcard SSL
5. **Enable rate limiting** per tenant
6. **Regular backups** dengan tenant-level restore capability

## Development

### Tenant Resolution Flow

```
Request -> ResolveTenant Middleware -> TenantService
    |
Subdomain? -> Custom Domain? -> Session? -> User?
    |
TenantScope Applied -> Query Filtered by tenant_id
```

### Adding New Tenant-Aware Model

```php
<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class NewModel extends Model
{
    use HasTenant; // Auto-apply TenantScope

    protected $fillable = ["tenant_id", "name", "data"];
}
```

### Creating Policy (Fail-Closed)

```php
<?php

namespace App\Policies;

class NewModelPolicy
{
    public function viewAny(User $user): bool
    {
        // 1. Block super admin from tenant data
        if ($user->isSuperAdmin()) {
            return false;
        }
        
        // 2. Check tenant membership
        return $user->tenant_id !== null;
    }

    public function view(User $user, NewModel $model): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }
        
        // Tenant isolation check
        if ((int) $user->tenant_id !== (int) $model->tenant_id) {
            return false;
        }
        
        return true;
    }
}
```

## Roadmap

```
✅ Platform Layer         — Frozen
✅ Academic Core           — Frozen
✅ Pesantren Core          — Frozen
✅ Diniyah Pack            — Frozen
▶️ Modern Pack             — In Progress
⏳ Salafiyah Pack          — Planned
⏳ Pesantren Quran & Tahfidz Pack — Planned
⏳ Formal Pack             — Planned
⏳ Terpadu Pack            — Planned
```

### Architecture Principles

- **Build once** — No major refactor later
- **Type-based architecture** — Gunakan `type` column, bukan tabel per variasi
- **Single controller/service per entity** — No proliferation
- **Dynamic program handling** — No hardcoded slugs
- **Fail-closed security** — Default deny

---

## Production Checklist

- [ ] APP_DOMAIN environment variable set
- [ ] Wildcard DNS A record configured
- [ ] SSL certificates for wildcard domain
- [ ] Database backup strategy implemented
- [ ] Monitoring for tenant-specific metrics
- [ ] Rate limiting configured per tenant
- [ ] Queue workers running
- [ ] Scheduled tasks configured
- [ ] Error tracking (Sentry/etc) integrated

## Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m "Add amazing feature"`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

**Development Requirements**:
- Follow PSR-12 coding standards
- Write tests untuk setiap feature baru
- Ensure all tests pass sebelum submit PR
- Update documentation untuk perubahan signifikan

## License

Distributed under the MIT License. See LICENSE for more information.

## Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission) - RBAC implementation
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript framework

---

**Status**: Production Ready | **Version**: 3.0.0 | **Last Updated**: June 2026
