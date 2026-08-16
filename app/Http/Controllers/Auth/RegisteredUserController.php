<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TenantProvisioningService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Creates a new user with an automatically generated tenant.
     * All operations are wrapped in a database transaction for atomicity.
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $provisioner = app(TenantProvisioningService::class);

        ['user' => $user] = $provisioner->provision([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        session(['tenant_id' => $user->tenant_id]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard.santri.index');
    }
}
