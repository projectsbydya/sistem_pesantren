@extends('layouts.tenant')

@section('title', 'Edit Ustadz')
@section('page-title', 'Edit Ustadz')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.ustadz.index') }}" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">Ustadz</a>
    <i class="fa-solid fa-chevron-right text-[8px]"></i>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

{{-- Back link --}}
<div class="mb-6">
    <a href="{{ tenant_route('dashboard.ustadz.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        Kembali ke daftar
    </a>
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">Edit Ustadz</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $ustadz->user?->name ?? '-' }}</p>
</div>

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="p-5">
        @if($errors->any())
            <x-alert type="error" class="mb-5">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ tenant_route('dashboard.ustadz.update', ['id' => $ustadz->id]) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section: Data Pribadi -->
            <div>
                <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                    <i class="fa-solid fa-user mr-1.5 text-emerald-500"></i>Data Pribadi
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $ustadz->user?->name) }}" required
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                        <input type="email" value="{{ $ustadz->user?->email }}" disabled
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-500 cursor-not-allowed">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Email tidak dapat diubah di sini.</p>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">No HP / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $ustadz->phone) }}"
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors"
                               placeholder="Contoh: 0812xxxxxxxx">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Digunakan sebagai pengirim notifikasi WhatsApp jika berperan sebagai Bendahara</p>
                    </div>
                </div>
            </div>

            <!-- Section: Info Pengajaran -->
            <div>
                <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                    <i class="fa-solid fa-chalkboard-user mr-1.5 text-emerald-500"></i>Info Pengajaran
                </h3>
                @include('dashboard.ustadz.partials._form-pengajaran')
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ tenant_route('dashboard.ustadz.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </a>
                <x-btn type="submit" variant="primary" icon="fa-save">
                    Perbarui Data
                </x-btn>
            </div>
        </form>

    </div>
</div>

</div>{{-- max-w-3xl mx-auto --}}
@endsection
