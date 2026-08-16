<?php

use App\Models\Tenant;
use App\Services\TenantService;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant instance or tenant ID
     *
     * @param string|null $key  If null, returns Tenant model. If 'id', returns tenant_id.
     * @return Tenant|int|null
     */
    function tenant(?string $key = null): Tenant|int|null
    {
        if ($key === 'id') {
            return TenantService::getTenantId();
        }

        return TenantService::getTenant();
    }
}

if (!function_exists('tenant_id')) {
    /**
     * Get the current tenant ID
     */
    function tenant_id(): ?int
    {
        return TenantService::getTenantId();
    }
}

if (!function_exists('set_tenant')) {
    /**
     * Set the current tenant
     */
    function set_tenant(int|Tenant|null $tenant): void
    {
        if ($tenant === null) {
            TenantService::clear();
            return;
        }

        if ($tenant instanceof Tenant) {
            TenantService::setTenant($tenant);
        } else {
            TenantService::setTenantId($tenant);
        }
    }
}

if (!function_exists('clear_tenant')) {
    /**
     * Clear the current tenant context
     */
    function clear_tenant(): void
    {
        TenantService::clear();
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * Check if current user is super admin
     */
    function is_super_admin(): bool
    {
        return TenantService::isSuperAdmin();
    }
}
