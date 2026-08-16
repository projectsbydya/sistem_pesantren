<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replaces role:super_admin middleware.
 * Uses the 'manage-tenants' Gate which checks isSuperAdmin() — no hardcoded role string.
 */
class EnsureSuperAdminGate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!Gate::allows('manage-tenants')) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
