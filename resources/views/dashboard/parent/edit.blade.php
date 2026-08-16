@extends('layouts.tenant')

@section('title', 'Edit Orang Tua')
@section('page-title', 'Edit Orang Tua')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.parent.index') }}" class="hover:text-emerald-600">Orang Tua</a>
    <i class="fa-solid fa-chevron-right text-[8px]"></i>
    <span>Edit</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <a href="{{ tenant_route('dashboard.parent.index') }}" class="inline-flex items-center gap-2 text-[13px] text-gray-500 hover:text-emerald-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">Edit Orang Tua</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $parent->name }}</p>
    </div>
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

    <form method="POST" action="{{ tenant_route('dashboard.parent.update', ['id' => $parent->id]) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section: Data Pribadi -->
        <div>
            <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                <i class="fa-solid fa-user mr-1.5 text-emerald-500"></i>Data Pribadi
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $parent->name) }}" required
                           class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Relasi dengan Santri <span class="text-red-500">*</span>
                    </label>
                    <select name="relationship" required
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                        <option value="father" {{ old('relationship', $parent->relationship) === 'father' ? 'selected' : '' }}>Ayah</option>
                        <option value="mother" {{ old('relationship', $parent->relationship) === 'mother' ? 'selected' : '' }}>Ibu</option>
                        <option value="guardian" {{ old('relationship', $parent->relationship) === 'guardian' ? 'selected' : '' }}>Wali</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <i class="fa-solid fa-phone mr-1 text-gray-400"></i>Nomor Telepon
                    </label>
                    <input type="tel" name="phone" value="{{ old('phone', $parent->phone) }}"
                           class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <i class="fa-solid fa-envelope mr-1 text-gray-400"></i>Email
                    </label>
                    <input type="email" value="{{ $parent->email ?? ($parent->user?->email ?? 'Belum ada email') }}" disabled
                           class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-500 cursor-not-allowed">
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Email diubah melalui manajemen user</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <i class="fa-solid fa-location-dot mr-1 text-gray-400"></i>Alamat
                    </label>
                    <textarea name="address" rows="2"
                              class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors resize-none">{{ old('address', $parent->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section: Link ke Santri -->
        <div>
            <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                <i class="fa-solid fa-link mr-1.5 text-emerald-500"></i>Hubungkan dengan Santri
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Pilih Santri
                    </label>
                    <select name="santri_ids[]" multiple
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors"
                            size="5">
                        @foreach($santris as $santri)
                            <option value="{{ $santri->id }}" {{ in_array($santri->id, old('santri_ids', $linkedSantriIds)) ? 'selected' : '' }}>
                                {{ $santri->name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Hold Ctrl/Cmd untuk memilih multiple santri</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_primary" value="1" id="is_primary" {{ old('is_primary') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_primary" class="text-[13px] text-gray-700 dark:text-gray-300">
                        Jadikan wali utama untuk santri yang dipilih
                    </label>
                </div>
            </div>
        </div>

        <!-- Section: Status -->
        <div>
            <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                <i class="fa-solid fa-toggle-on mr-1.5 text-emerald-500"></i>Status
            </h3>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $parent->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-[13px] text-gray-700 dark:text-gray-300">
                    Aktif (bisa login dan menerima notifikasi)
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ tenant_route('dashboard.parent.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Batal
            </a>
            <x-btn type="submit" variant="primary" icon="fa-save">
                Perbarui Data
            </x-btn>
        </div>
    </form>

</x-card>

</div>{{-- max-w-3xl mx-auto --}}
@endsection
