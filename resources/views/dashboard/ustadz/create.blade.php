@extends('layouts.tenant')

@section('title', 'Tambah Ustadz')

@section('breadcrumb')
    <span><a href="{{ tenant_route('dashboard.ustadz.index') }}" class="hover:text-emerald-600">Ustadz & Karyawan</a></span>
    <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
    <span>Tambah Baru</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">

<!-- Header -->
<div class="mb-6">
    <a href="{{ tenant_route('dashboard.ustadz.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke daftar ustadz
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Tambah Ustadz Baru</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Data master ustadz. Penugasan mengajar diatur melalui modul Akademik.</p>
</div>

<x-card>
    @if($errors->any())
        <x-alert type="error" class="mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    @if(session('success'))
        <x-alert type="success" class="mb-6">
            {{ session('success') }}
        </x-alert>
        
        <!-- CTA After Success -->
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-6 text-center mb-6">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-check text-emerald-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100 mb-2">Ustadz Berhasil Dibuat!</h3>
            <p class="text-sm text-emerald-700 dark:text-emerald-300 mb-4">Apa yang ingin Anda lakukan selanjutnya?</p>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ tenant_route('dashboard.ustadz.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <i class="fa-solid fa-list"></i>
                    Kembali ke Daftar Ustadz
                </a>
                <a href="{{ tenant_route('dashboard.akademik.penugasan.create', ['programSlug' => 'diniyah']) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    Buat Penugasan Mengajar
                </a>
            </div>

            <!-- Download Credentials -->
            <div class="mt-4 pt-4 border-t border-emerald-200 dark:border-emerald-800">
                <a href="{{ tenant_route('dashboard.ustadz.credentials.download') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg hover:bg-emerald-200 dark:hover:bg-emerald-900/50 transition-colors">
                    <i class="fa-solid fa-download"></i>
                    Download Kredensial Login (Excel)
                </a>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Segera download - kredensial hanya tersedia sekali
                </p>
            </div>
        </div>
    @else

    <form method="POST" action="{{ tenant_route('dashboard.ustadz.store') }}" class="space-y-6">
        @csrf

        <!-- Section: Data Pribadi -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-user mr-2 text-emerald-600"></i>Data Pribadi
            </h3>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="Nama lengkap ustadz">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="Email (kosongkan untuk auto-generate)">
                    <p class="text-xs text-gray-400 mt-1">Jika dikosongkan, email akan dibuat otomatis</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        No HP / WhatsApp
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="Contoh: 0812xxxxxxxx">
                </div>
            </div>
        </div>

        <!-- Section: Info Pengajaran -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-chalkboard-user mr-2 text-emerald-600"></i>Info Pengajaran
            </h3>
            @include('dashboard.ustadz.partials._form-pengajaran')
        </div>

        <!-- Section: Akun Login -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-key mr-2 text-emerald-600"></i>Akun Login
            </h3>
            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="auto_create_account" value="1" id="auto_create_account" checked
                           class="w-4 h-4 mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <div class="flex-1">
                        <label for="auto_create_account" class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            Buat akun login otomatis
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Akun akan dibuat dengan email unik dan password random. 
                            Kredensial dapat diunduh setelah data tersimpan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info: Penugasan Mengajar -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Penugasan Mengajar</p>
                    <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                        Penugasan kelas dan mata pelajaran diatur melalui modul 
                        <a href="{{ tenant_route('dashboard.akademik.penugasan.index', ['programSlug' => 'diniyah']) }}" class="font-medium underline">Akademik > Penugasan</a>
                        setelah ustadz dibuat.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ tenant_route('dashboard.ustadz.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                <i class="fa-solid fa-save mr-1.5"></i>
                Simpan Ustadz
            </button>
        </div>
    </form>
    @endif
</x-card>

</div>{{-- max-w-2xl mx-auto --}}
@endsection
