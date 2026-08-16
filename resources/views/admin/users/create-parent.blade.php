<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat User Orang Tua - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-indigo-600 text-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold">Buat User Orang Tua</h1>
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
                <form method="POST" action="{{ route('dashboard.admin.users.parent.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenant / Pesantren</label>
                        <select name="tenant_id" id="tenant_id" required class="w-full border rounded px-3 py-2"
                                onchange="loadParentsWithoutUser()">
                            <option value="">Pilih Tenant</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Orang Tua (yang belum punya akun)</label>
                        <select name="parent_id" id="parent_id" required class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Tenant dulu...</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1" id="parentInfo"></p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
                        <p class="text-sm text-yellow-800">
                            <strong>Info:</strong> Email akan di-generate otomatis: ortu-{id}@{tenant}.parent.local<br>
                            Password akan di-generate otomatis dan ditampilkan setelah pembuatan.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700">
                            Buat User Orang Tua
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

    <script>
        async function loadParentsWithoutUser() {
            const tenantId = document.getElementById('tenant_id').value;
            const parentSelect = document.getElementById('parent_id');
            const parentInfo = document.getElementById('parentInfo');

            if (!tenantId) {
                parentSelect.innerHTML = '<option value="">Pilih Tenant dulu...</option>';
                parentInfo.textContent = '';
                return;
            }

            parentSelect.innerHTML = '<option value="">Loading...</option>';

            try {
                const response = await fetch(`{{ route('dashboard.admin.users.parent.without-user') }}?tenant_id=${tenantId}`);
                const parents = await response.json();

                if (parents.length === 0) {
                    parentSelect.innerHTML = '<option value="">Semua orang tua sudah punya akun</option>';
                    parentInfo.textContent = 'Tidak ada orang tua yang perlu dibuatkan akun.';
                    return;
                }

                parentSelect.innerHTML = parents.map(p =>
                    `<option value="${p.id}">${p.name} (${p.santri_count} anak) - ${p.phone || 'no phone'}</option>`
                ).join('');

                parentInfo.textContent = `${parents.length} orang tua belum punya user account.`;
            } catch (error) {
                parentSelect.innerHTML = '<option value="">Error loading data</option>';
                parentInfo.textContent = 'Gagal memuat data orang tua.';
            }
        }
    </script>
</body>
</html>
