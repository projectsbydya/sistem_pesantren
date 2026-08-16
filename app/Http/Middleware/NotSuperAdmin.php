<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If user is super admin, redirect them to dashboard
        // (which will redirect them to super-admin panel)
        if ($user && $user->isSuperAdmin()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
