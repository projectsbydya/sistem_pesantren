<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Admin - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-indigo-600 text-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold">Buat Admin Baru</h1>
                    <a href="{{ route('dashboard.admin.users.index') }}" class="text-sm hover:underline">← Kembali</a>
                </div>
            </div>
        </nav>

        <div class="max-w-2xl mx-auto px-4 py-8">
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('dashboard.admin.users.admin.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenant / Pesantren</label>
                        <select name="tenant_id" required class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Tenant</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Admin</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border rounded px-3 py-2" placeholder="Ustadz Ahmad">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (opsional)</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full border rounded px-3 py-2" placeholder="admin@example.com">
                        <p class="text-sm text-gray-500 mt-1">Kosongkan untuk auto-generate: admin.{nama}@{tenant}.local</p>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="auto_generate_email" value="1" class="mr-2">
                            <span class="text-sm text-gray-700">Auto-generate email</span>
                        </label>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
                        <p class="text-sm text-yellow-800">
                            <strong>Info:</strong> Password akan di-generate otomatis dan ditampilkan setelah pembuatan.
                            Pastikan untuk mencatat atau screenshot password tersebut.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            Buat Admin
                        </button>
                        <a href="{{ route('dashboard.admin.users.index') }}"
                           class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
