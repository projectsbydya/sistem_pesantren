<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

/**
 * Permission Registrar — USER → ROLE → RELATION → RESOURCE
 *
 * Register all application permissions as Gates.
 * This replaces hardcoded role checks with permission-based authorization.
 */
class PermissionRegistrar
{
    /**
     * Register all application permissions as Gates.
     * Uses Spatie hasRole() — no hardcoded role string comparisons.
     */
    public static function register(): void
    {
        // =========================================================================
        // Super Admin Gate — manages the SaaS platform itself
        // =========================================================================
        Gate::define('manage-tenants', fn ($user) => $user->isSuperAdmin());

        // =========================================================================
        // Santri Resource Gates
        // USER → ROLE/RELATION → Santri
        // =========================================================================
        Gate::define('view-santri', fn ($user) =>
            $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA, Role::USTADZ])
            || $user->parent !== null
            || $user->santri !== null
        );

        Gate::define('create-santri', fn ($user) => $user->hasRole(Role::TENANT_ADMIN));
        Gate::define('update-santri', fn ($user) => $user->hasRole(Role::TENANT_ADMIN));
        Gate::define('delete-santri', fn ($user) => $user->hasRole(Role::TENANT_ADMIN));

        // =========================================================================
        // Ustadz Resource Gates
        // USER → ROLE/RELATION → Ustadz
        // =========================================================================
        Gate::define('view-ustadz', fn ($user) =>
            $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null
        );

        Gate::define('create-ustadz', fn ($user) => $user->hasRole(Role::TENANT_ADMIN));
        Gate::define('update-ustadz', fn ($user) => $user->hasRole(Role::TENANT_ADMIN));
        Gate::define('delete-ustadz', fn ($user) => $user->hasRole(Role::TENANT_ADMIN));

        // =========================================================================
        // Akademik Gates
        // =========================================================================
        Gate::define('view-kelas', fn ($user) =>
            $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null
        );

        Gate::define('manage-kelas', fn ($user) => $user->hasRole(Role::TENANT_ADMIN));

        Gate::define('view-nilai', fn ($user) =>
            $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA, Role::USTADZ])
            || $user->parent !== null
            || $user->santri !== null
        );

        Gate::define('input-nilai', fn ($user) =>
            $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null
        );

        // =========================================================================
        // Keuangan (SPP/Tabungan) Gates
        // USER → ROLE (Bendahara or Admin) → RESOURCE
        // =========================================================================
        Gate::define('manage-finances', fn ($user) =>
            $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])
        );

        Gate::define('view-spp', fn ($user) =>
            $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA]) || $user->parent !== null
        );

        Gate::define('manage-spp', fn ($user) =>
            $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])
        );

        Gate::define('view-tabungan', fn ($user) =>
            $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA]) || $user->parent !== null
        );

        Gate::define('manage-tabungan', fn ($user) =>
            $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])
        );

        // =========================================================================
        // Absensi Gates
        // =========================================================================
        Gate::define('view-absensi', fn ($user) =>
            $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null || $user->parent !== null
        );

        Gate::define('input-absensi', fn ($user) =>
            $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null
        );

        // =========================================================================
        // Staff Module (SDM) Gates
        // =========================================================================
        Gate::define('access-staff-module', fn ($user) =>
            $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null
        );

        // =========================================================================
        // Tenant Switching — tenant users only, not super admin
        // =========================================================================
        Gate::define('switch-tenant', fn ($user) =>
            !$user->isSuperAdmin() && $user->tenant_id !== null
        );
    }

    /**
     * Seed default roles and permissions using Spatie's native API.
     * Idempotent — safe to run multiple times.
     */
    public static function seed(): void
    {
        // Ensure all permission records exist first
        $allPermissions = [
            Permission::VIEW_SANTRI, Permission::CREATE_SANTRI, Permission::UPDATE_SANTRI, Permission::DELETE_SANTRI,
            Permission::VIEW_USTADZ, Permission::CREATE_USTADZ, Permission::UPDATE_USTADZ, Permission::DELETE_USTADZ,
            Permission::VIEW_KELAS, Permission::MANAGE_KELAS,
            Permission::VIEW_NILAI, Permission::INPUT_NILAI,
            Permission::VIEW_ABSENSI, Permission::INPUT_ABSENSI,
            Permission::MANAGE_FINANCES, Permission::VIEW_SPP, Permission::MANAGE_SPP,
            Permission::VIEW_TABUNGAN, Permission::MANAGE_TABUNGAN,
            Permission::MANAGE_TENANTS,
        ];

        foreach ($allPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Ensure all role records exist
        $roleNames = [
            Role::SUPER_ADMIN, Role::TENANT_ADMIN, Role::BENDAHARA,
            Role::USTADZ, Role::PARENT, Role::SANTRI,
        ];

        foreach ($roleNames as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Tenant Admin — full operational access
        Role::findByName(Role::TENANT_ADMIN)->syncPermissions([
            Permission::VIEW_SANTRI, Permission::CREATE_SANTRI, Permission::UPDATE_SANTRI, Permission::DELETE_SANTRI,
            Permission::VIEW_USTADZ, Permission::CREATE_USTADZ, Permission::UPDATE_USTADZ, Permission::DELETE_USTADZ,
            Permission::VIEW_KELAS, Permission::MANAGE_KELAS,
            Permission::VIEW_NILAI, Permission::INPUT_NILAI,
            Permission::VIEW_ABSENSI, Permission::INPUT_ABSENSI,
            Permission::MANAGE_FINANCES, Permission::VIEW_SPP, Permission::MANAGE_SPP,
            Permission::VIEW_TABUNGAN, Permission::MANAGE_TABUNGAN,
        ]);

        // Bendahara — finance + read-only santri/nilai
        Role::findByName(Role::BENDAHARA)->syncPermissions([
            Permission::VIEW_SANTRI, Permission::VIEW_NILAI,
            Permission::MANAGE_FINANCES,
            Permission::VIEW_SPP, Permission::MANAGE_SPP,
            Permission::VIEW_TABUNGAN, Permission::MANAGE_TABUNGAN,
        ]);

        // Ustadz — teaching operations (per-record access via Policies)
        Role::findByName(Role::USTADZ)->syncPermissions([
            Permission::VIEW_SANTRI, Permission::VIEW_USTADZ,
            Permission::VIEW_KELAS,
            Permission::VIEW_NILAI, Permission::INPUT_NILAI,
            Permission::VIEW_ABSENSI, Permission::INPUT_ABSENSI,
        ]);
    }
}
