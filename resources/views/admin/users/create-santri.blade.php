<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat User Santri - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-indigo-600 text-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold">Buat User Santri</h1>
                    <a href="{{ route('dashboard.admin.users.index') }}" class="text-sm hover:underline">← Kembali</a>
                </div>
            </div>
        </nav>

        <div class="max-w-2xl mx-auto px-4 py-8">
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

            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('dashboard.admin.users.santri.store') }}" id="santriForm">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenant / Pesantren</label>
                        <select name="tenant_id" id="tenant_id" required class="w-full border rounded px-3 py-2"
                                onchange="loadSantriWithoutUser()">
                            <option value="">Pilih Tenant</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Santri (yang belum punya akun)</label>
                        <select name="santri_id" id="santri_id" required class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Tenant dulu...</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1" id="santriCount"></p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
                        <p class="text-sm text-yellow-800">
                            <strong>Info:</strong> Email akan di-generate otomatis: santri-{nis}@{tenant}.student.local<br>
                            Password akan di-generate otomatis dan ditampilkan setelah pembuatan.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                            Buat User Santri
                        </button>
                        <a href="{{ route('dashboard.admin.users.index') }}"
                           class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                            Batal
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bulk Create Section -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="font-semibold mb-3">Bulk Create</h3>
                <p class="text-sm text-gray-600 mb-4">Buat user account untuk semua santri yang belum punya akun di tenant ini.</p>

                <form method="POST" action="{{ route('dashboard.admin.users.bulk.santri') }}" id="bulkForm">
                    @csrf
                    <input type="hidden" name="tenant_id" id="bulk_tenant_id">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700"
                            onclick="return confirm('Yakin ingin membuat user untuk semua santri yang belum punya akun?')">
                        Bulk Create Santri Users
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        async function loadSantriWithoutUser() {
            const tenantId = document.getElementById('tenant_id').value;
            const santriSelect = document.getElementById('santri_id');
            const countInfo = document.getElementById('santriCount');
            const bulkTenantId = document.getElementById('bulk_tenant_id');

            bulkTenantId.value = tenantId;

            if (!tenantId) {
                santriSelect.innerHTML = '<option value="">Pilih Tenant dulu...</option>';
                countInfo.textContent = '';
                return;
            }

            santriSelect.innerHTML = '<option value="">Loading...</option>';

            try {
                const response = await fetch(`{{ route('dashboard.admin.users.santri.without-user') }}?tenant_id=${tenantId}`);
                const santris = await response.json();

                if (santris.length === 0) {
                    santriSelect.innerHTML = '<option value="">Semua santri sudah punya akun</option>';
                    countInfo.textContent = 'Tidak ada santri yang perlu dibuatkan akun.';
                    return;
                }

                santriSelect.innerHTML = santris.map(s =>
                    `<option value="${s.id}">${s.nis} - ${s.name}</option>`
                ).join('');

                countInfo.textContent = `${santris.length} santri belum punya user account.`;
            } catch (error) {
                santriSelect.innerHTML = '<option value="">Error loading data</option>';
                countInfo.textContent = 'Gagal memuat data santri.';
            }
        }
    </script>
</body>
</html>
