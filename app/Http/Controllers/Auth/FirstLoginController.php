<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\TenantUrlHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * First Login Controller
 *
 * Handles password change for users who are logging in for the first time
 * or have been forced to reset their password by an admin.
 * This is a REQUIRED step before accessing the main application.
 */
class FirstLoginController extends Controller
{
    /**
     * Show the password change form.
     * This is displayed when must_change_password is true.
     */
    public function show()
    {
        $user = auth()->user();

        // If user doesn't need to change password, redirect to dashboard
        if (!$user->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.password-change', [
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the password change submission.
     * Validates and updates the user's password.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        // Double-check that password change is required
        if (!$user->must_change_password) {
            return redirect()->route('dashboard');
        }

        // Validate input
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()     // Require both uppercase and lowercase
                    ->numbers()       // Require at least one number
                    ->symbols(),      // Require at least one symbol
                'confirmed',          // Require password_confirmation field
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.mixed_case' => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers' => 'Password harus mengandung angka.',
            'password.symbols' => 'Password harus mengandung simbol (!@#$%^&*).',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Verify current password
        if (!password_verify($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak benar.',
            ]);
        }

        // Prevent reusing the same password
        if (password_verify($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password baru tidak boleh sama dengan password lama.',
            ]);
        }

        // Update password and clear the must_change_password flag
        $user->update([
            'password' => bcrypt($validated['password']),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        // Log the successful password change
        \Illuminate\Support\Facades\Log::info('Password changed on first login', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'ip' => $request->ip(),
        ]);

        // Redirect to dashboard with success message
        // Tenant users must go to their subdomain dashboard
        if ($user->tenant_id) {
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if ($tenant) {
                return redirect()->to(
                    TenantUrlHelper::tenantUrlWithPort($tenant, '/dashboard')
                )->with('success', 'Password berhasil diubah. Selamat datang!');
            }
        }

        return redirect()->route('dashboard')
            ->with('success', 'Password berhasil diubah. Selamat datang!');
    }
}
