<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasTenant
{
    /**
     * Boot the trait - apply global scope and hooks
     */
    public static function bootHasTenant(): void
    {
        // Add global tenant scope
        static::addGlobalScope(new TenantScope);

        // Auto-inject tenant_id when creating
        static::creating(function (Model $model) {
            // Only set tenant_id if it's not already set
            if (empty($model->tenant_id)) {
                $tenantId = TenantService::getTenantId();

                // Set tenant_id if tenant context is available
                // Super admins MUST have tenant context set via setTenant() before creating
                // This prevents orphaned records (records with no tenant_id)
                if ($tenantId !== null) {
                    $model->tenant_id = $tenantId;
                }
                // If tenantId is null, the database will throw an error (if nullable=false)
                // or create an orphaned record (if nullable=true) - both are blocked by scope
            }
        });
    }

    
    /**
     * Get the tenant relationship
     */
    public function tenant()
    {
        
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Scope: Remove tenant filter entirely.
     * RESTRICTED — super admin or console only.
     */
    public function scopeWithoutTenant(Builder $query): Builder
    {
        static::assertCrosstenantAccess();

        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope: Filter by an explicit tenant ID, bypassing the global scope.
     * RESTRICTED — super admin or console only.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        static::assertCrosstenantAccess();

        return $query->withoutGlobalScope(TenantScope::class)
                     ->where('tenant_id', $tenantId);
    }

    /**
     * Assert that the caller is either a super admin or running in console.
     * Aborts with 403 in any other HTTP context — fail-closed.
     */
    protected static function assertCrosstenantAccess(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $user = Auth::user();

        if ($user && $user->isSuperAdmin()) {
            return;
        }

        abort(403, 'Cross-tenant query not permitted.');
    }

    /**
     * Scope: Explicitly filter by current tenant context.
     * Returns no rows if tenant context is missing (fail-closed).
     */
    public function scopeCurrentTenant(Builder $query): Builder
    {
        $tenantId = TenantService::getTenantId();

        if ($tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->withoutGlobalScope(TenantScope::class)
                     ->where('tenant_id', $tenantId);
    }
}
