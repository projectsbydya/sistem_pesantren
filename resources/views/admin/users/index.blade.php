<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <nav class="bg-indigo-600 text-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold">User Management - Super Admin</h1>
                    <div class="flex gap-4">
                        <a href="{{ route('dashboard.super-admin.index') }}" class="text-sm hover:underline">← Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex gap-3 mb-6">
                <a href="{{ route('dashboard.admin.users.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Buat Admin
                </a>
                <a href="{{ route('dashboard.admin.users.santri.create') }}"
                   class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    + Buat User Santri
                </a>
                <a href="{{ route('dashboard.admin.users.parent.create') }}"
                   class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    + Buat User Orang Tua
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="GET" action="{{ route('dashboard.admin.users.index') }}" class="flex gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tenant</label>
                        <select name="tenant_id" class="mt-1 border rounded px-3 py-2 w-48">
                            <option value="">Semua Tenant</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ $tenantId == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select name="role" class="mt-1 border rounded px-3 py-2 w-40">
                            <option value="">Semua Role</option>
                            <option value="super_admin" {{ $role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin" {{ $role == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="parent" {{ $role == 'parent' ? 'selected' : '' }}>Orang Tua</option>
                            <option value="student" {{ $role == 'student' ? 'selected' : '' }}>Santri</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Filter
                    </button>
                    @if($tenantId || $role)
                        <a href="{{ route('dashboard.admin.users.index') }}" class="text-gray-600 hover:underline">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $user->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $user->tenant?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $roleClass = match($user->role) {
                                            'super_admin' => 'bg-red-100 text-red-800',
                                            'admin' => 'bg-blue-100 text-blue-800',
                                            'parent' => 'bg-purple-100 text-purple-800',
                                            'student' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs {{ $roleClass }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded text-xs {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <form method="POST" action="{{ route('dashboard.admin.users.reset-password', $user->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-yellow-600 hover:underline text-xs"
                                                onclick="return confirm('Reset password untuk {{ $user->name }}? Password baru akan ditampilkan.')">
                                            Reset Password
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.admin.users.toggle-active', $user->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:underline text-xs">
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    Tidak ada user yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="px-6 py-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
