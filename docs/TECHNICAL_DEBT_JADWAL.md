# Technical Debt: Jadwal Legacy Fields

**Status:** Documented  
**Priority:** Medium  
**Impact:** Data consistency, maintenance overhead  
**Created:** 2026-06-07

## Summary

Tabel `jadwal` masih menyimpan field legacy string (`kelas`, `mata_pelajaran`) karena kolom `subject_id` tidak ada. Refactor penuh memerlukan migration schema baru.

## Current State

### Schema Jadwal (Production)

```
| Column            | Type        | Nullable | Notes                    |
|-------------------|-------------|----------|--------------------------|
| id                | bigint      | NO       | PK                       |
| tenant_id         | bigint      | NO       | FK → tenants             |
| ustadz_id         | bigint      | YES      | FK → ustadz (nullable)   |
| kelas_id          | bigint      | YES      | FK → kelas ✅            |
| ustadz_kelas_id   | bigint      | YES      | FK → ustadz_kelas        |
| mata_pelajaran    | varchar     | NO       | ⚠️ Legacy string         |
| kelas             | varchar     | NO       | ⚠️ Legacy string         |
| hari              | enum        | NO       | Senin–Ahad               |
| jam_mulai         | time        | NO       |                          |
| jam_selesai       | time        | NO       |                          |
| program_id        | bigint      | NO       | FK → programs ✅         |
| created_at        | timestamp   | YES      |                          |
| updated_at        | timestamp   | YES      |                          |

❌ subject_id: DOES NOT EXIST
```

### Masalah

1. **Data redundancy**: `mata_pelajaran` string disimpan padahal bisa di-resolve via `subject_id` → `subjects.name`
2. **Data inconsistency risk**: Jika `subjects.name` diubah, `jadwal.mata_pelajaran` tidak otomatis update
3. **Query complexity**: Tidak bisa eager load subject relationship dari jadwal

### Workaround Saat Ini (Onboarding)

```php
// OnboardingController::storeJadwal
Schedule::create([
    'tenant_id'       => $tenantId,
    'program_id'      => $kelas->program_id,
    'ustadz_kelas_id' => null,
    'ustadz_id'       => null,
    'kelas_id'        => $kelasId,        // ✅ FK exists
    'mata_pelajaran'  => $subject->name,  // ⚠️ Legacy string
    'kelas'           => $kelas->name,    // ⚠️ Legacy string (redundant dengan kelas_id)
    'hari'            => $hari,
    'jam_mulai'       => $jamMulai,
    'jam_selesai'     => $jamSelesai,
]);
```

## Rekomendasi Fix (Future)

### Option A: Add subject_id (Minimal)
```php
// Migration
Schema::table('jadwal', function (Blueprint $table) {
    $table->foreignId('subject_id')->nullable()->after('kelas_id')
          ->constrained('subjects')->nullOnDelete();
    $table->index(['kelas_id', 'subject_id', 'hari']);
});

// Backfill
UPDATE jadwal j
JOIN subjects s ON s.name = j.mata_pelajaran AND s.tenant_id = j.tenant_id
SET j.subject_id = s.id;
```

### Option B: Full Refactor (Ideal)
1. Add `subject_id` (nullable)
2. Backfill data
3. Make `mata_pelajaran` virtual/generated column OR drop
4. Make `kelas` virtual/generated column OR drop (redundant dengan `kelas_id`)

### Option C: Keep Status Quo
- Dokumentasikan constraint
- Accept technical debt untuk Phase 1E freeze
- Address di Phase 2 refactor

## Decision

**Pilih Option C untuk sekarang.**

Alasan:
- Phase 1E freeze — no major schema changes
- System works with current design
- Risk acceptable (name changes rare in production)
- Full refactor better done in dedicated maintenance window

## Related Files

- `@/app/Http/Controllers/Dashboard/OnboardingController.php::storeJadwal()`
- `@/app/Http/Controllers/Dashboard/ScheduleController.php`
- `@/app/Models/Schedule.php`
- `@/database/migrations/2026_04_30_060000_create_jadwal_table.php`
- `@/database/migrations/2026_04_30_200001_refactor_jadwal_table.php`
