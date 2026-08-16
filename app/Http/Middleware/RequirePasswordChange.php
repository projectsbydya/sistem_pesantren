<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce password change for first-time login.
 *
 * This middleware should be applied AFTER the 'auth' middleware.
 * It redirects users with must_change_password=true to the password change page.
 */
class RequirePasswordChange
{
    /**
     * Routes that should be accessible even when password change is required.
     * These are typically auth-related routes (login, logout, password change itself).
     */
    protected array $except = [
        'password/change',           // The password change page
        'password/update-first',     // The password update endpoint
        'logout',                    // Allow logout
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if not authenticated (shouldn't happen if middleware ordered correctly)
        if (!$user) {
            return $next($request);
        }

        // Skip for Super Admin (they are trusted and don't need forced changes)
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if current route is in the exception list
        $currentPath = $request->path();
        foreach ($this->except as $exceptPath) {
            if (str_starts_with($currentPath, $exceptPath)) {
                return $next($request);
            }
        }

        // Enforce password change
        if ($user->must_change_password) {
            // Allow access to the password change page
            if ($request->routeIs('password.change') || $request->routeIs('password.update-first')) {
                return $next($request);
            }

            // API requests return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You must change your password before continuing.',
                    'redirect' => route('password.change'),
                ], 403);
            }

            // Redirect to password change page with flash message
            return redirect()->route('password.change')
                ->with('warning', 'Anda harus mengubah password sebelum melanjutkan.');
        }

        return $next($request);
    }
}
