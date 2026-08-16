<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Injects tenant context into every log entry for the duration of the request.
 * Adds a request_id for distributed tracing.
 *
 * Usage: add 'tenant.log' to any middleware group in bootstrap/app.php
 *        or attach globally via the web/api groups.
 *
 * Log output includes:
 *   tenant_id, tenant_slug, user_id, request_id, method, path
 */
class TenantRequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = (string) Str::uuid();

        // Read tenant context — use getTenantId() (no DB query) for ID.
        // Only read slug if the model is already in memory (avoid triggering a query from logger).
        $tenantId   = TenantService::getTenantId();
        // getCachedTenant() returns the model ONLY if already in memory — no DB query
        $tenantSlug = TenantService::getCachedTenant()?->slug;

        // Attach context to all log entries in this request lifecycle
        Log::withContext([
            'request_id' => $requestId,
            'tenant_id'  => $tenantId,
            'tenant_slug'=> $tenantSlug,
            'user_id'    => auth()->id(),
            'method'     => $request->method(),
            'path'       => $request->path(),
        ]);

        // Set X-Request-ID response header for client-side correlation
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
