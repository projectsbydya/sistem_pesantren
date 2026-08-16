<?php

namespace App\Helpers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

/**
 * Tenant URL Helper
 *
 * Centralized tenant subdomain URL generation.
 * ALL domain logic reads from config('app.app_domain') and config('app.scheme').
 * ZERO hardcoded domains — works for any environment without code changes.
 */
class TenantUrlHelper
{
    /**
     * Generate full tenant subdomain URL.
     *
     * Uses config('app.app_domain') for base domain and config('app.scheme') for protocol.
     * Output: {scheme}://{slug}.{app_domain}{path}
     *
     * @param Tenant|string $tenant Tenant model or slug string
     * @param string $path Optional path (e.g. '/dashboard')
     * @param array $query Optional query params
     * @return string Full URL e.g. http://pondok1.pesantren.test/dashboard
     *
     * @throws \InvalidArgumentException If slug format is invalid
     */
    public static function tenantUrl(Tenant|string $tenant, string $path = '', array $query = []): string
    {
        $slug = $tenant instanceof Tenant ? $tenant->slug : $tenant;

        if (!self::isValidSlug($slug)) {
            throw new \InvalidArgumentException("Invalid tenant slug: {$slug}");
        }

        $domain = Config::get('app.app_domain');
        $scheme = Config::get('app.scheme');

        $url = "{$scheme}://{$slug}.{$domain}";

        if ($path !== '') {
            $url .= '/' . ltrim($path, '/');
        }

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    /**
     * Generate tenant URL with port support (for local development).
     *
     * Uses configured domain and scheme only - no fallbacks to localhost.
     * Port is only used if explicitly provided.
     *
     * @param Tenant|string $tenant Tenant model or slug
     * @param string $path Optional path
     * @param int|null $port Port number (null = no port)
     * @return string Full URL with port if provided
     */
    public static function tenantUrlWithPort(
        Tenant|string $tenant,
        string $path = '',
        ?int $port = null
    ): string {
        $slug = $tenant instanceof Tenant ? $tenant->slug : $tenant;

        if (!self::isValidSlug($slug)) {
            throw new \InvalidArgumentException("Invalid tenant slug: {$slug}");
        }

        $domain = Config::get('app.app_domain');
        $scheme = Config::get('app.scheme');

        // Build host with explicit port only
        $host = "{$slug}.{$domain}";
        if ($port !== null && $port !== 80 && $port !== 443) {
            $host .= ':' . $port;
        }

        $url = "{$scheme}://{$host}";

        if ($path !== '') {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    /**
     * Validate tenant slug format (DNS label compliant).
     *
     * Rules: alphanumeric + dash/underscore, starts with letter/number,
     * min 1 char, max 63 chars (DNS label limit).
     */
    public static function isValidSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9_-]{0,62}$/i', $slug) === 1;
    }

    /**
     * Generate an absolute URL on the MAIN domain (no subdomain).
     *
     * Uses config('app.app_domain') and config('app.scheme') only.
     * Output: {scheme}://{app_domain}{path}
     *
     * @param string $path Optional path (e.g. '/login')
     * @param int|null $port Port number (null = no port)
     * @return string e.g. http://pesantren.test/login
     */
    public static function mainDomainUrl(string $path = '', ?int $port = null): string
    {
        $domain = Config::get('app.app_domain');
        $scheme = Config::get('app.scheme');

        $host = $domain;
        if ($port !== null && $port !== 80 && $port !== 443) {
            $host .= ':' . $port;
        }

        $url = "{$scheme}://{$host}";

        if ($path !== '') {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    /**
     * Find and validate a tenant by ID. Aborts with 403 if not found.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function getValidatedTenant(int $tenantId): Tenant
    {
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            abort(403, 'Tenant tidak ditemukan atau tidak aktif.');
        }

        return $tenant;
    }
}
