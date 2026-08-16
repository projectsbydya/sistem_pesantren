<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('tenant')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_super_admin', false)
            ->latest()
            ->paginate(20);

        return view('dashboard.admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('dashboard.admin.users.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'ustadz', 'parent', 'student'])],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'tenant_id' => auth()->user()->tenant_id,
            'is_super_admin' => false,
        ]);

        return redirect()
            ->route('dashboard.admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('dashboard.admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['admin', 'ustadz', 'parent', 'student'])],
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => [
                    'string',
                    Password::min(8)->mixedCase()->numbers()->symbols(),
                    'confirmed',
                ],
            ]);
            $data['password'] = Hash::make($request->password);
            $data['password_changed_at'] = null;
        }

        $user->update($data);

        if ($request->filled('password')) {
            $user->requirePasswordChange();
        }

        return redirect()
            ->route('dashboard.admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('dashboard.admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('resetPassword', $user);

        $request->validate([
            'password' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers()->symbols(),
                'confirmed',
            ],
        ]);

        $user->update([
            'password'            => Hash::make($request->password),
            'password_changed_at' => null,
        ]);

        $user->requirePasswordChange();

        return redirect()->back()
            ->with('success', 'Password berhasil direset. User wajib ganti password saat login berikutnya.');
    }
}
