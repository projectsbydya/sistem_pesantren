# Migration Order Documentation

## Urutan Eksekusi Migration

Berikut adalah urutan migration yang direkomendasikan untuk sistem pesantren multi-tenant:

### 1. System Foundation (000001-000010)
- `000001_create_super_admins_table.php` - Super Admin
- `000002_create_tenants_table.php` - Tenant/Pesantren
- `000003_create_tenant_settings_table.php` - Konfigurasi Tenant
- `000004_create_branches_table.php` - Cabang Pesantren
- `000005_create_plans_table.php` - Paket Langganan
- `000006_create_subscriptions_table.php` - Subscription Tenant
- `000007_create_invoices_table.php` - Invoice SaaS
- `000008_create_payments_table.php` - Pembayaran SaaS
- `000009_create_subscription_logs_table.php` - Log Subscription
- `000010_create_system_settings_table.php` - Pengaturan Global

### 2. Feature Management (000011-000016)
- `000011_create_features_table.php` - Fitur Sistem
- `000012_create_plan_features_table.php` - Fitur per Paket
- `000013_create_usage_limits_table.php` - Batas Penggunaan
- `000014_create_analytics_logs_table.php` - Log Analytics
- `000015_create_tenant_features_table.php` - Override Fitur Tenant
- `000016_create_usage_logs_table.php` - Log Penggunaan

### 3. User Management (000017-000019)
- `000017_create_roles_table.php` - Role Pengguna
- `000018_create_users_table.php` - Pengguna
- `000019_create_user_roles_table.php` - Role Pengguna

### 4. Academic Foundation (000020-000029)
- `000020_create_santri_table.php` - Data Santri
- `000021_create_santri_cards_table.php` - Kartu Santri (QR/RFID)
- `000022_create_riwayat_pendidikan_table.php` - Riwayat Pendidikan
- `000023_create_classes_table.php` - Kelas
- `000024_create_subjects_table.php` - Mata Pelajaran
- `000025_create_ustadz_table.php` - Data Ustadz
- `000026_create_schedules_table.php` - Jadwal Pelajaran
- `000027_create_grades_table.php` - Nilai Akademik
- `000028_create_hafalan_targets_table.php` - Target Hafalan
- `000029_create_hafalan_progress_table.php` - Progress Hafalan

### 5. Financial Management (000030-000031)
- `000030_create_bills_table.php` - Tagihan Santri
- `000031_create_santri_payments_table.php` - Pembayaran Santri

### 6. Dormitory Management (000032-000033)
- `000032_create_rooms_table.php` - Kamar Asrama
- `000033_create_room_members_table.php` - Penghuni Kamar

### 7. Attendance System (000034-000035)
- `000034_create_attendance_table.php` - Absensi Santri
- `000035_create_attendance_ustadz_table.php` - Absensi Ustadz

### 8. Discipline System (000036-000037)
- `000036_create_violations_table.php` - Pelanggaran
- `000037_create_permissions_table.php` - Perizinan

### 9. Health Management (000038)
- `000038_create_health_records_table.php` - Rekam Medis

### 10. Cooperative System (000039-000042)
- `000039_create_products_table.php` - Produk Koperasi
- `000040_create_transactions_table.php` - Transaksi
- `000041_create_transaction_items_table.php` - Detail Transaksi
- `000042_create_wallets_table.php` - Dompet Digital

### 11. Communication System (000043-000045)
- `000043_create_announcements_table.php` - Pengumuman
- `000044_create_messages_table.php` - Pesan Pribadi
- `000045_create_notifications_table.php` - Notifikasi

### 12. Gamification System (000046-000047)
- `000046_create_badges_table.php` - Badge/Penghargaan
- `000047_create_santri_badges_table.php` - Badge Santri

### 13. Calendar System (000048)
- `000048_create_events_table.php` - Kalender Acara

### 14. Alumni & Donations (000049-000050)
- `000049_create_alumni_table.php` - Data Alumni
- `000050_create_donations_table.php` - Donasi

## Cara Menjalankan Migration

### Jalankan Semua Migration
```bash
php artisan migrate
```

### Jalankan Migration Per Modul
```bash
# System Foundation
php artisan migrate --path=database/migrations/000001_create_super_admins_table.php
php artisan migrate --path=database/migrations/000002_create_tenants_table.php
# ... dan seterusnya
```

### Rollback Migration
```bash
# Rollback satu langkah
php artisan migrate:rollback

# Rollback semua
php artisan migrate:reset
```

### Fresh Migration
```bash
# Hapus semua data dan migrate ulang
php artisan migrate:fresh
```

## Notes Penting

1. **Foreign Key Constraints**: Semua foreign key menggunakan `onDelete('cascade')` atau `onDelete('restrict')` sesuai kebutuhan
2. **Indexing**: Setiap tabel memiliki index untuk performa query optimal
3. **Enum Fields**: Menggunakan enum untuk fields dengan nilai tetap (status, type, dll)
4. **Tenant Isolation**: Semua data tenant-related memiliki `tenant_id` untuk multi-tenancy
5. **Soft Deletes**: Tidak menggunakan soft deletes secara default, dapat ditambahkan jika diperlukan
6. **Timestamps**: Semua tabel menggunakan `timestamps()` untuk created_at dan updated_at

## Best Practices

1. Selalu backup database sebelum menjalankan migration di production
2. Test migration di development/staging environment terlebih dahulu
3. Gunakan transaction untuk migration yang kompleks
4. Monitor performance setelah migration, terutama untuk tabel besar
5. Pertimbangkan untuk menggunakan database seeding untuk data awal
