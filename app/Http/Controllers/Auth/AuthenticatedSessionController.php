<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\TenantUrlHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        // Super Admin: stay on main domain, redirect to super-admin dashboard
        if ($user->is_super_admin) {
            return redirect()->intended(route('dashboard.super-admin.index'));
        }

        // Tenant Admin/User: validate tenant dan redirect ke subdomain
        if ($user->tenant_id) {
            // Validasi tenant - abort 403 jika tidak ditemukan
            $tenant = TenantUrlHelper::getValidatedTenant($user->tenant_id);

            // Set session tenant
            session(['tenant_id' => $tenant->id]);

            // Generate tenant URL dengan port support untuk local dev
            $tenantUrl = TenantUrlHelper::tenantUrlWithPort($tenant, '/dashboard');

            // Redirect ke subdomain tenant
            return redirect()->to($tenantUrl, 302);
        }

        // Fallback: redirect ke main dashboard (untuk user tanpa tenant)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     *
     * Redirects to the MAIN domain login page regardless of which subdomain
     * the logout was submitted from. Uses config('app.app_domain') — no hardcoded domains.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Always redirect to main domain /login, not the current subdomain.
        return redirect()->to(TenantUrlHelper::mainDomainUrl('/login'));
    }
}
