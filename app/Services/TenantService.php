<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class TenantService
{
    private static ?int $currentTenantId = null;
    private static ?Tenant $currentTenant = null;
    /** Cached isSuperAdmin result, keyed by the user ID it was computed for. */
    private static ?int  $isSuperAdminUserId = null;
    private static ?bool $isSuperAdminCache   = null;

    /**
     * Set the current tenant ID
     */
    public static function setTenantId(?int $tenantId): void
    {
        self::$currentTenantId = $tenantId;
        self::$currentTenant = null; // Reset cached tenant
    }

    /**
     * Get the current tenant ID
     */
    public static function getTenantId(): ?int
    {
        return self::$currentTenantId;
    }

    /**
     * Set the current tenant model
     */
    public static function setTenant(?Tenant $tenant): void
    {
        self::$currentTenant = $tenant;
        self::$currentTenantId = $tenant?->id;
    }

    /**
     * Get the current tenant model
     */
    public static function getTenant(): ?Tenant
    {
        if (self::$currentTenant === null && self::$currentTenantId !== null) {
            self::$currentTenant = Tenant::find(self::$currentTenantId);

            // If tenant no longer exists in DB, clear the stale state
            if (self::$currentTenant === null) {
                self::$currentTenantId = null;
            }
        }

        return self::$currentTenant;
    }

    /**
     * Check if a tenant is currently set
     */
    public static function hasTenant(): bool
    {
        return self::$currentTenantId !== null;
    }

    /**
     * Return the tenant model ONLY if already loaded in memory.
     * Never fires a DB query — safe to call from loggers, observers, scope apply().
     */
    public static function getCachedTenant(): ?Tenant
    {
        return self::$currentTenant;
    }

    /**
     * Clear all per-request state.
     * MUST be called at the start of every request (ResolveTenant middleware).
     */
    public static function clear(): void
    {
        self::$currentTenantId    = null;
        self::$currentTenant      = null;
        self::$isSuperAdminCache   = null;
        self::$isSuperAdminUserId  = null;
    }

    /**
     * Check if the current user is a super admin.
     * Result is cached for the lifetime of the request — Auth::user() is only
     * called once regardless of how many times isSuperAdmin() is invoked
     * (e.g. from TenantScope on every Eloquent query in a single request).
     */
    public static function isSuperAdmin(): bool
    {
        $currentId = Auth::id(); // cheap session/token read — no DB query

        // Cache hit: same user as when we last computed this value
        if (self::$isSuperAdminCache !== null && self::$isSuperAdminUserId === $currentId) {
            return self::$isSuperAdminCache;
        }

        // Cache miss or user changed — recompute
        if (!$currentId) {
            self::$isSuperAdminUserId = null;
            return self::$isSuperAdminCache = false;
        }

        self::$isSuperAdminUserId = $currentId;
        return self::$isSuperAdminCache = (bool) (Auth::user()?->is_super_admin ?? false);
    }

    /**
     * Check if we should apply tenant scope
     * (skip if super admin and no specific tenant is forced)
     */
    public static function shouldApplyScope(): bool
    {
        // Always apply if we're not authenticated
        if (!Auth::check()) {
            return true;
        }

        // Super admins can view all tenants unless a specific tenant is set
        if (self::isSuperAdmin()) {
            // If super admin hasn't selected a specific tenant, don't filter
            return self::hasTenant();
        }

        return true;
    }

    /**
     * Resolve tenant from request and store it.
     * Called by TenantMiddleware.
     * Always clears first to prevent state bleed between requests.
     */
    public static function resolve(\Illuminate\Http\Request $request): void
    {
        self::clear(); // MUST always clear — prevents cross-request data leak

        $tenant = self::resolveFromRequest($request);

        if ($tenant) {
            self::setTenant($tenant);
        }
    }

    /**
     * Bootstrap tenant context for a queue job or artisan command.
     * Must be called explicitly at the start of any non-HTTP job.
     */
    public static function forJob(?int $tenantId): void
    {
        self::clear();

        if ($tenantId !== null) {
            self::setTenantId($tenantId);
        }
    }

    /**
     * Validate tenant access and return status with optional error message.
     *
     * @return array{canAccess: bool, error?: string, status?: int}
     */
    public static function validateTenantAccess(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? self::getTenant();

        if (!$tenant) {
            return [
                'canAccess' => false,
                'error' => 'Anda tidak memiliki akses ke tenant ini.',
                'status' => 403,
            ];
        }

        // Check tenant is active
        if (!$tenant->is_active) {
            return [
                'canAccess' => false,
                'error' => 'Tenant is inactive or suspended.',
                'status' => 403,
            ];
        }

        // Check trial expiration
        if ($tenant->is_trial && $tenant->trial_ends_at && now()->gt($tenant->trial_ends_at)) {
            return [
                'canAccess' => false,
                'error' => 'Trial period has expired.',
                'status' => 403,
            ];
        }

        return ['canAccess' => true];
    }

    /**
     * Check if tenant can be accessed (convenience method).
     */
    public static function canAccessTenant(?Tenant $tenant = null): bool
    {
        return self::validateTenantAccess($tenant)['canAccess'];
    }

    /**
     * Resolve tenant from request (subdomain or header) — returns model only, no side-effects.
     */
    public static function resolveFromRequest(?\Illuminate\Http\Request $request = null): ?Tenant
    {
        $request = $request ?? request();

        // Priority 1: X-Tenant-ID header
        $tenantId = $request->header('X-Tenant-ID');
        if ($tenantId && is_numeric($tenantId)) {
            return Tenant::find((int) $tenantId);
        }

        // Priority 2: Subdomain or domain match
        // Resolution order for host:
        //  a) $request->server('HTTP_HOST') — set correctly when TestCase::call() rewrites URI
        //  b) $request->headers->get('host') — set directly in unit tests via request()->headers->set()
        //  c) $request->getHost() — Symfony fallback (may be overridden by URL)
        $rawServerHost = $request->server('HTTP_HOST');
        $host = $rawServerHost
            ? strtolower(preg_replace('/:\d+$/', '', trim($rawServerHost)))
            : $request->getHost();

        if ($host) {
            // Reject obviously invalid hosts to prevent probe attacks
            if (strlen($host) > 253 || !preg_match('/^[a-z0-9.\-]+$/', $host)) {
                return null;
            }

            $appDomain = config('app.app_domain');
            
            // Strict subdomain matching: {slug}.{app_domain}
            if (str_ends_with($host, '.' . $appDomain)) {
                $slug = substr($host, 0, -(strlen($appDomain) + 1));
                if ($slug && $slug !== 'www') {
                    $tenant = Tenant::where('slug', $slug)->first();
                    if ($tenant) {
                        return $tenant;
                    }
                }
            }

            // Custom domain: exact match on tenant.domain column
            $tenant = Tenant::where('domain', $host)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        return null;
    }
}
