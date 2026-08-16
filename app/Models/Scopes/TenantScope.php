<?php

namespace App\Models\Scopes;

use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Behavior:
     * - Tenant context set → always filter by tenant_id (normal multi-tenant operation)
     * - Authenticated user, no tenant context → block queries (fail-closed, prevents cross-tenant leaks)
     * - Super admin, no tenant context → allow all (cross-tenant tools)
     * - Unauthenticated, no tenant context → allow all (login/registration/public routes)
     *   Security for public routes is enforced by middleware, not by the scope.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // The User model is the auth identity — the session guard loads it BEFORE tenant
        // context exists. Applying TenantScope here creates an infinite loop:
        //   SessionGuard::user() → User::find() → TenantScope → Auth::user() → User::find() → ∞
        // User ↔ tenant isolation is enforced by owns.tenant middleware instead.
        if ($model instanceof \App\Models\User) {
            return;
        }

        // Fast path: tenant context already set — always wins, no auth check needed
        $tenantId = TenantService::getTenantId();
        if ($tenantId !== null) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
            return;
        }

        // Super admin without a forced tenant → show all (cross-tenant tools)
        // Use getCachedTenant() / isSuperAdmin() — no DB queries
        if (TenantService::isSuperAdmin()) {
            return;
        }

        // Auth::id() is a cheap session/token read — does NOT load the User model
        // Avoids the User → HasTenant → TenantScope → Auth::user() → User recursion
        $userId = Auth::id();

        if ($userId === null) {
            // Unauthenticated: allow queries — public routes (login, registration)
            // Security enforced by route middleware, not scope
            return;
        }

        // Authenticated but no tenant context set yet.
        // Use the guard's already-resolved user if available (avoids a second query).
        // Auth::user() is safe here because: if the User model is being loaded right now
        // (the recursive scenario), Auth::user() returns null from the guard cache,
        // so we fall through to the whereRaw('1 = 0') safe-close below.
        $user = Auth::user();

        if ($user && $user->tenant_id) {
            $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
            return;
        }

        // Authenticated user with no tenant_id — block queries (fail-closed)
        $builder->whereRaw('1 = 0');
    }
}
