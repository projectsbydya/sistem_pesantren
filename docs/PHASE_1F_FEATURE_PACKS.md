# Phase 1F — Program Feature Packs

## Context

Phase 1E froze the universal module set:
**Program → Kelas → Subject → Ustadz → Santri → Jadwal → Nilai → Absensi → Materi → Elearning → Raport**

Not all programs support hafalan. Mixing hafalan into the universal dependency and onboarding engine would break programs that do not need it (e.g., Bahasa, Madrasah).

Phase 1F introduces **Program Feature Packs**: optional capability bundles that attach to specific program types without modifying the universal core.

---

## Design Principles

1. **Zero modification to universal core** — `FeatureDependencyService`, `TenantSetupService`, `TenantSetupProgress`, `RedirectIfOnboardingIncomplete`, and `academic_programs.php#features` are untouched.
2. **Opt-in per program** — a `Program` record declares which Feature Packs it supports. A tenant only sees pack UI if their programs support it.
3. **Pluggable, not hardcoded** — packs register themselves; the sidebar, policy gates, and dependency checks all read from the registry at runtime.
4. **Tenant-isolated** — every pack query respects `tenant_id`.

---

## New Concepts

### `ProgramFeaturePack` (value object / config entry)

Each pack has:
- `key` — unique string identifier (`tahfidz`, `salafiyah`, `bahasa`)
- `name` — display name
- `modules` — list of module keys this pack adds (e.g., `hafalan_quran`, `target_hafalan`)
- `supported_program_slugs` — the programs that activate this pack (stored on `programs` table via `feature_packs` JSON column)

### `programs.feature_packs` (migration)

```sql
ALTER TABLE programs ADD COLUMN feature_packs JSON NULL;
-- e.g.: ["tahfidz", "salafiyah"]
```

A program's supported packs are declared by the super admin when creating/editing the global `Program` record.

### `ProgramFeaturePackService` (new service)

```php
class ProgramFeaturePackService
{
    /**
     * Get all pack keys active for this tenant
     * (union of packs from all active tenant programs)
     */
    public static function getActivePacks(?int $tenantId = null): array;

    /**
     * Check if a specific pack is active for this tenant
     */
    public static function hasPack(string $packKey, ?int $tenantId = null): bool;

    /**
     * Get all modules active for this tenant across all packs
     */
    public static function getActiveModules(?int $tenantId = null): array;
}
```

### Navigation integration (no universal core change)

`NavigationGateService` gets new methods that delegate to `ProgramFeaturePackService`:

```php
public function canViewKepesantrenanSection(): bool
{
    // Phase 1F: replaces the current HafalanQuran model check
    return ProgramFeaturePackService::hasPack('tahfidz')
        || ProgramFeaturePackService::hasPack('salafiyah');
}
```

The `Kepesantrenan` sidebar section already exists and is gated by `canViewKepesantrenan()`. Phase 1F simply changes what that gate resolves to — from a policy check on HafalanQuran to a pack registry check.

### Sidebar pack injection

The sidebar's `kepesantrenan` menu items can be generated dynamically from `academic_programs.kepesantrenan` filtered by `ProgramFeaturePackService::getActivePacks()`, instead of being statically listed.

---

## Pack Definitions

### Tahfidz Pack (`tahfidz`)

| Module | Routes prefix | Model |
|--------|--------------|-------|
| Hafalan Quran | `kepesantrenan.hafalan-quran` | `HafalanQuran` |
| Target Hafalan | `kepesantrenan.target-hafalan` | `TargetHafalan` |

Programs: `pesantren-quran-tahfidz`

### Salafiyah Pack (`salafiyah`)

| Module | Routes prefix | Model |
|--------|--------------|-------|
| Hafalan Kitab | `kepesantrenan.hafalan-kitab` | `HafalanKitab` |
| Target Hafalan | `kepesantrenan.target-hafalan` | `TargetHafalan` |

Programs: `salafiyah`

### Bahasa Pack (`bahasa`) — future

| Module | Notes |
|--------|-------|
| Placement Test | New module |
| Speaking Assessment | New module |

Programs: `modern`, `terpadu` (bahasa-focused tenants)

---

## Migration Path for Existing Hafalan Data

1. Add `feature_packs` JSON column to `programs` table (nullable, default `[]`).
2. Seed existing programs with their packs:
   - `pesantren-quran-tahfidz` → `["tahfidz"]`
   - `salafiyah` → `["salafiyah"]`
3. `ProgramFeaturePackService::getActivePacks()` reads from `programs.feature_packs` via `tenant_program` join.
4. All existing `HafalanQuran`, `HafalanKitab`, `TargetHafalan` data is preserved — the pack service only controls UI visibility and navigation gating, not data existence.

---

## What Phase 1F Does NOT Touch

- `FeatureDependencyService` — no hafalan dependency rules added
- `TenantSetupService` / `TenantSetupProgress` — no hafalan setup steps
- `RedirectIfOnboardingIncomplete` — no hafalan redirect logic
- Universal routes, controllers, and models (Kelas, Subject, Jadwal, Nilai, Absensi, Materi, Elearning, Raport)

---

## Implementation Order (Phase 1F)

1. Migration: `programs.feature_packs` JSON column
2. Seeder: populate existing programs with their pack keys
3. `ProgramFeaturePackService` — `getActivePacks()`, `hasPack()`, `getActiveModules()`
4. Update `NavigationGateService::canViewKepesantrenanSection()` to use pack registry
5. Update `academic_programs.kepesantrenan` config to be filtered by active packs at runtime
6. Super Admin program edit UI: pack selection checkboxes
7. Repeat for Bahasa pack once modules are built
