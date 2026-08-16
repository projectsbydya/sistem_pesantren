# Refactor: Sistem Private (Admin-Managed Users)

Dokumen ini menjelaskan refactor dari sistem public registration menjadi sistem private dimana hanya Super Admin yang bisa membuat user account.

---

## 1. Perubahan yang Dilakukan

### 1.1 Disable Public Registration

| File | Perubahan |
|------|-----------|
| `routes/auth.php` | Comment route `register`, redirect ke `/login` |
| `routes/web.php` | Comment route `/register/pesantren`, `/register/santri`, `/register/orang-tua` |
| Views | Hapus link "Daftar" dari halaman login |

### 1.2 File Baru Dibuat

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       └── UserManagementController.php    # Controller untuk manage users
│   └── Middleware/
│       └── EnsureSuperAdmin.php              # Middleware cek super_admin
├── Services/
│   └── UserProvisioningService.php             # Service create user + auto-generate email/password
database/
└── migrations/
    └── 2025_04_25_000000_add_soft_deletes_and_indices.php
resources/
└── views/
    └── admin/
        └── users/
            ├── index.blade.php       # List users
            ├── create-admin.blade.php
            ├── create-santri.blade.php
            └── create-parent.blade.php
```

### 1.3 Routes Baru (Super Admin Only)

```
dashboard/admin/users                    GET    - List users
dashboard/admin/users/admin/create     GET    - Form buat admin
dashboard/admin/users/admin            POST   - Store admin
dashboard/admin/users/santri/create    GET    - Form buat user santri
dashboard/admin/users/santri           POST   - Store santri user
dashboard/admin/users/parent/create    GET    - Form buat user orang tua
dashboard/admin/users/parent           POST   - Store parent user
dashboard/admin/users/bulk-santri      POST   - Bulk create santri users
api/santri-without-user                GET    - AJAX: list santri tanpa user
api/parents-without-user               GET    - AJAX: list parents tanpa user
```

---

## 2. Struktur Database (Final)

### 2.1 Relasi Antar Tabel

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│     tenants     │       │      users      │       │     santris     │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id (PK)         │◄──────┤ tenant_id (FK)  │       │ id (PK)         │
│ name            │       │ id (PK)         │◄──────┤ user_id (FK)    │
│ slug            │       │ role            │       │ tenant_id (FK)  │
│ email           │       │ email (unique)  │       │ nis (unique)    │
└─────────────────┘       │ password        │       │ name            │
                          │ is_active       │       │ parent_id (FK)  │
                          └─────────────────┘       └─────────────────┘
                                   │                           │
                                   │                           │
                                   │                 ┌─────────────────┐
                                   │                 │    parents      │
                                   │                 ├─────────────────┤
                                   └────────────────►│ id (PK)         │
                                                     │ user_id (FK)    │
                                                     │ tenant_id (FK)  │
                                                     │ name            │
                                                     │ phone           │
                                                     └─────────────────┘
                                                               │
                                                               │
                                          ┌────────────────────┘
                                          │
                              ┌─────────────────────┐
                              │   parent_santri     │  ← Pivot Table
                              ├─────────────────────┤
                              │ id (PK)             │
                              │ parent_id (FK)      │
                              │ santri_id (FK)      │
                              │ relationship        │  (father/mother/guardian)
                              │ is_primary          │  (boolean)
                              └─────────────────────┘
```

### 2.2 Penjelasan Relasi

| Relasi | Cardinality | Keterangan |
|--------|-------------|------------|
| `users` → `tenants` | N:1 | Setiap user belongs to satu tenant (kecuali super_admin) |
| `users` → `santri` | 1:1 | Santri punya satu user account (nullable) |
| `users` → `parents` | 1:1 | Parent punya satu user account (nullable) |
| `santri` → `parents` | N:M via `parent_santri` | Satu santri bisa punya multiple wali (ayah, ibu, guardian) |
| `santri` → `parents` (primary) | N:1 | `santri.parent_id` = wali utama untuk notifikasi |

### 2.3 Constraint Penting

```sql
-- Unique constraint untuk mencegah duplicate
ALTER TABLE users ADD UNIQUE INDEX idx_tenant_email (tenant_id, email);
ALTER TABLE santris ADD UNIQUE INDEX idx_tenant_nis (tenant_id, nis);
ALTER TABLE parent_santri ADD UNIQUE INDEX idx_parent_santri (parent_id, santri_id);

-- Index untuk performa
ALTER TABLE users ADD INDEX idx_tenant_role_active (tenant_id, role, is_active);
ALTER TABLE santris ADD INDEX idx_user_tenant (user_id, tenant_id);
ALTER TABLE parents ADD INDEX idx_user_tenant (user_id, tenant_id);
```

---

## 3. Flow Pembuatan User

### 3.1 Flow: Create Admin User

```
┌─────────────┐     ┌──────────────────────┐     ┌─────────────────────┐
│ Super Admin │────►│ UserManagement       │────►│ UserProvisioning    │
│ (Browser)   │     │ Controller           │     │ Service             │
└─────────────┘     └──────────────────────┘     └─────────────────────┘
       │                      │                            │
       │ POST /users/admin      │                            │
       │ {name, tenant_id}    │                            │
       │─────────────────────►│                            │
       │                      │─────────────────────────────►│
       │                      │  provisionAdmin()          │
       │                      │                            │
       │                      │◄─────────────────────────────│
       │                      │  {user, password}           │
       │                      │                            │
       │◄─────────────────────│  redirect with flash       │
       │                      │  (show email + password)   │
       ▼                      ▼                            ▼
```

**Email Generated:** `admin.{nama}@{tenant-slug}.local`
**Password Generated:** Random 10 chars (lowercase + uppercase + numbers)

### 3.2 Flow: Create Santri User

```
┌─────────────┐     ┌──────────────────────┐     ┌─────────────────────┐     ┌──────────────┐
│ Super Admin │────►│ UserManagement       │────►│ UserProvisioning    │────►│   Santri     │
│ (Browser)   │     │ Controller           │     │ Service             │     │   Model      │
└─────────────┘     └──────────────────────┘     └─────────────────────┘     └──────────────┘
       │                      │                            │                     │
       │ POST /users/santri    │                            │                     │
       │ {santri_id}          │                            │                     │
       │─────────────────────►│                            │                     │
       │                      │─────────────────────────────│                     │
       │                      │  createSantriUser()          │                     │
       │                      │                            │                     │
       │                      │◄─────────────────────────────│                     │
       │                      │  {user, password}           │                     │
       │                      │                            │─────────────────────►│
       │                      │                            │  update user_id      │
       │                      │                            │◄─────────────────────│
       │                      │                            │                     │
       │◄─────────────────────│  redirect with flash       │                     │
       │                      │  (show email + password)   │                     │
       ▼                      ▼                            ▼                     ▼
```

**Email Generated:** `santri-{nis}@{tenant-slug}.student.local`
**Action:** User dibuat → `santri.user_id` di-update

### 3.3 Flow: Create Parent User

**Email Generated:** `ortu-{parent-id}@{tenant-slug}.parent.local`
**Action:** User dibuat → `parents.user_id` di-update

---

## 4. Auto-Generate Email & Password

### 4.1 Email Pattern

| Role | Pattern | Contoh |
|------|---------|--------|
| Admin | `admin.{nama-slug}@{tenant-slug}.local` | `admin.ahmad@al-ihsan.local` |
| Santri | `santri-{nis}@{tenant-slug}.student.local` | `santri-2024001@al-ihsan.student.local` |
| Parent | `ortu-{parent-id}@{tenant-slug}.parent.local` | `ortu-15@al-ihsan.parent.local` |

### 4.2 Password Pattern

```php
// 10 karakter: lowercase + uppercase + number
// Contoh: aK7mP2xR9q

public function generatePassword(int $length = 10): string
{
    $password = [
        $lowercase[random_int(0, 25)],  // minimal 1 lowercase
        $uppercase[random_int(0, 25)],  // minimal 1 uppercase
        $numbers[random_int(0, 9)],      // minimal 1 number
    ];
    // ... sisanya random
}
```

### 4.3 Uniqueness Check

```php
// Jika email sudah ada, tambahkan counter:
// admin.ahmad@al-ihsan.local
// admin.ahmad1@al-ihsan.local  (jika sudah ada)
// admin.ahmad2@al-ihsan.local  (jika sudah ada)
```

---

## 5. Best Practices yang Diterapkan

### 5.1 RBAC (Role-Based Access Control)

```php
// User model: role check methods
$user->isSuperAdmin();  // role = super_admin OR is_super_admin = true
$user->isAdmin();       // role = admin
$user->isParent();      // role = parent
$user->isStudent();     // role = student
$user->hasRole('admin');
```

**Middleware:**
- `role:super_admin` - Hanya super admin bisa akses
- `role:admin` - Admin dan super admin bisa akses
- `role:parent` - Orang tua bisa akses
- `parent.santri` - Custom: hanya bisa akses data anak sendiri

### 5.2 Multi-Tenant Security

```php
// Setiap query selalu filter by tenant_id
Santri::where('tenant_id', $tenantId)->get();

// Atau pakai TenantScope (global scope)
Santri::all(); // auto-filter: WHERE tenant_id = current_tenant

// Super admin bisa bypass
Santri::withoutGlobalScopes()->get(); // semua tenant
```

### 5.3 Transaction Safety

```php
// Semua create user dalam transaction
DB::transaction(function () {
    // 1. Create user
    $user = User::create([...]);
    
    // 2. Link ke profile (santri/parent)
    $santri->update(['user_id' => $user->id]);
    
    // 3. Dispatch email job (after commit)
    SendCredentialsEmail::dispatch($user, $password)->afterCommit();
    
    return $user;
});
```

### 5.4 Soft Deletes (Opsional)

```php
// Migration: tambah softDeletes()
$table->softDeletes();

// Model: use SoftDeletes trait
class Santri extends Model {
    use SoftDeletes;
}

// Query
Santri::all();           // hanya aktif
Santri::withTrashed()->all(); // termasuk dihapus
Santri::onlyTrashed()->all(); // hanya yang dihapus
```

---

## 6. Checklist Implementasi

### Phase 1: Disable Public Registration
- [x] Comment routes di `routes/auth.php`
- [x] Comment routes di `routes/web.php`
- [ ] Hapus link register dari views (login page, welcome page)

### Phase 2: User Management System
- [x] Create `UserProvisioningService`
- [x] Create `UserManagementController`
- [x] Create `EnsureSuperAdmin` middleware
- [x] Add routes di `routes/web.php`
- [x] Create views (index, create-admin, create-santri, create-parent)

### Phase 3: Database Optimization
- [x] Create migration: soft deletes + indices
- [ ] Run migration: `php artisan migrate`

### Phase 4: Testing
- [ ] Test: Super Admin bisa akses `/dashboard/admin/users`
- [ ] Test: Non-super admin tidak bisa akses (403)
- [ ] Test: Buat admin user → cek email & password tampil
- [ ] Test: Buat santri user → cek `santri.user_id` terupdate
- [ ] Test: Buat parent user → cek `parents.user_id` terupdate
- [ ] Test: Bulk create santri users
- [ ] Test: Reset password → password baru tampil

### Phase 5: Deployment
- [ ] Backup database
- [ ] Deploy code
- [ ] Run migration
- [ ] Test semua flow di production
- [ ] Monitor error logs

---

## 7. Catatan Penting

### 7.1 Password Display
Password yang di-generate **hanya ditampilkan sekali** setelah pembuatan. Setelah itu tidak bisa dilihat lagi (karena di-hash). Jika lupa, harus reset password.

### 7.2 Email Delivery (TODO)
Kode saat ini memiliki placeholder untuk email:
```php
// TODO: Dispatch job untuk kirim email kredensial
// SendUserCredentials::dispatch($user, $password)->afterCommit();
```
Implementasikan sesuai dengan email provider yang digunakan (Mailgun, SendGrid, SMTP, dll).

### 7.3 First Super Admin
Pastikan ada minimal satu super_admin di database sebelum disable public registration:

```sql
-- Cek apakah ada super_admin
SELECT * FROM users WHERE role = 'super_admin' OR is_super_admin = 1;

-- Jika belum ada, buat dulu:
INSERT INTO users (name, email, password, role, is_super_admin, is_active, created_at, updated_at)
VALUES ('Super Admin', 'super@admin.com', '$2y$10$...', 'super_admin', 1, 1, NOW(), NOW());
```

---

## 8. Troubleshooting

### Issue: Tidak bisa akses user management
**Cek:**
1. User sudah login: `auth()->check()`
2. Role super_admin: `auth()->user()->isSuperAdmin()`
3. Route benar: `route('dashboard.admin.users.index')`
4. Middleware terdaftar di `app/Http/Kernel.php`

### Issue: Santri/Parent tidak muncul di dropdown
**Cek:**
1. Santri/Parent sudah dibuat di tenant tersebut
2. Santri/Parent belum punya user_id (belum punya account)
3. AJAX endpoint return JSON benar

### Issue: Duplicate email error
**Cek:**
1. Email pattern memang unique per tenant
2. Jika ingin custom email, pastikan belum dipakai user lain di tenant yang sama
3. Gunakan auto-generate email untuk menghindari conflict

---

**Dibuat:** 25 April 2026  
**Versi:** 1.0
