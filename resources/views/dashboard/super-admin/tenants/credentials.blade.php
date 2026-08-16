@extends('layouts.dashboard')

@section('title', 'Kredensial Tenant Baru')
@section('page-title', 'Kredensial Tenant Baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-alert type="warning" title="Penting!" dismissible="false">
        Simpan informasi login ini dengan aman. Password tidak akan ditampilkan lagi setelah meninggalkan halaman ini.
    </x-alert>

    <x-super-admin.page-header
        title="Tenant Berhasil Dibuat"
        subtitle="Berikut adalah informasi login untuk admin tenant."
        backUrl="{{ route('dashboard.super-admin.tenants.index') }}"/>

    <!-- Credentials Card -->
    <x-card title="Informasi Login" class="overflow-hidden">
        <div class="space-y-6">
            <!-- Tenant Info -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Pesantren</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-lg font-semibold text-gray-900">{{ $credentials['tenant_name'] }}</p>
                    <p class="text-sm text-gray-500">Slug: {{ $tenant->slug }}</p>
                </div>
            </div>

            <!-- Admin Credentials -->
            <div>
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Akun Admin</h4>

                @php
                    $loginUrl = config('app.scheme') . '://' . config('app.app_domain') . '/login';
                @endphp

                <div class="space-y-4">
                    <!-- Login URL -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">URL Login</label>
                            <p class="text-sm font-mono text-gray-900">{{ $loginUrl }}</p>
                        </div>
                        <button onclick="copyToClipboard('{{ $loginUrl }}')" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">
                            Salin
                        </button>
                    </div>

                    <!-- Email -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Email</label>
                            <p class="text-base font-mono text-gray-900">{{ $credentials['admin_email'] }}</p>
                        </div>
                        <button onclick="copyToClipboard('{{ $credentials['admin_email'] }}')" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">
                            Salin
                        </button>
                    </div>

                    <!-- Password -->
                    <div class="flex items-center justify-between bg-red-50 rounded-lg p-4 border-2 border-red-200">
                        <div>
                            <label class="block text-xs font-medium text-red-600 uppercase">Password (Simpan!)</label>
                            <p class="text-xl font-mono font-bold text-red-700" id="password">{{ $credentials['admin_password'] }}</p>
                        </div>
                        <button onclick="copyToClipboard('{{ $credentials['admin_password'] }}')" class="text-red-600 hover:text-red-700 font-medium text-sm">
                            Salin
                        </button>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <x-alert type="info" title="Langkah Selanjutnya">
                <ol class="list-decimal list-inside space-y-1">
                    <li>Admin akan login dengan email dan password di atas</li>
                    <li>Sistem akan <strong>memaksa</strong> admin untuk mengubah password pada login pertama</li>
                    <li>Password baru harus minimal 8 karakter dengan huruf besar, kecil, angka, dan simbol</li>
                </ol>
            </x-alert>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-between">
                <x-btn href="{{ route('dashboard.super-admin.tenants.index') }}" icon="fa-arrow-left" variant="outline">Kembali ke Daftar Tenant</x-btn>
                <form method="POST" action="{{ route('dashboard.super-admin.tenants.reset-password', $tenant) }}" class="inline"
                      onsubmit="return confirm('Yakin ingin membuat password baru? Password lama akan hilang.');">
                    @csrf
                    <x-btn type="submit" icon="fa-sync" variant="warning">Generate Password Baru</x-btn>
                </form>
            </div>
        </x-slot:footer>
    </x-card>

    <p class="text-center text-sm text-gray-500">
        Untuk keamanan, password di atas hanya ditampilkan sekali.
        Jika password hilang, gunakan fitur "Generate Password Baru" atau "Reset Password" di halaman edit tenant.
    </p>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Teks disalin ke clipboard');
    }).catch(function(err) {
        console.error('Gagal menyalin:', err);
    });
}
</script>
@endsection
