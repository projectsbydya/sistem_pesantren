@extends('layouts.tenant')

@section('title', 'Edit Transaksi Tabungan')

@section('content')
<div class="max-w-xl mx-auto">

<div class="mb-6">
    <a href="{{ tenant_route('dashboard.tabungan.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Edit Transaksi Tabungan</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $tabungan->santri?->name }}</p>
</div>

<x-card>
    @if($errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    @php
        $oldSantriId = old('santri_id', $tabungan->santri_id);
        $oldKelasId = $oldSantriId ? optional($santriList->firstWhere('id', (int) $oldSantriId))->kelas_id : null;
    @endphp

    <form method="POST" action="{{ tenant_route('dashboard.tabungan.update', ['tabungan' => $tabungan->id]) }}" class="space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kelas</label>
            <select id="kelas_id"
                    class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                <option value="">Pilih Kelas</option>
                @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" data-santri="{{ base64_encode(json_encode($kelas->santri->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values())) }}" {{ (int) $oldKelasId === $kelas->id ? 'selected' : '' }}>
                        {{ $kelas->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Santri <span class="text-red-500">*</span></label>
            <select name="santri_id" id="santri_id" required data-selected="{{ $oldSantriId }}"
                    class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                <option value="">Pilih Kelas dahulu</option>
            </select>
            @error('santri_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis Transaksi <span class="text-red-500">*</span></label>
            <select name="jenis" required
                    class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                <option value="setor" {{ old('jenis', $tabungan->jenis) === 'setor' ? 'selected' : '' }}>Setoran</option>
                <option value="tarik" {{ old('jenis', $tabungan->jenis) === 'tarik' ? 'selected' : '' }}>Penarikan</option>
            </select>
            @error('jenis')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="jumlah" value="{{ old('jumlah', $tabungan->jumlah) }}" required min="1"
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                @error('jumlah')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" value="{{ old('tanggal', $tabungan->tanggal?->format('Y-m-d')) }}" required
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                @error('tanggal')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Keterangan</label>
            <input type="text" name="keterangan" value="{{ old('keterangan', $tabungan->keterangan) }}"
                   class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.tabungan.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-save mr-1.5"></i> Perbarui
            </button>
        </div>
    </form>
</x-card>

</div>

<script>
    (function () {
        const kelasSelect = document.getElementById('kelas_id');
        const santriSelect = document.getElementById('santri_id');

        function populateSantri(kelasOption) {
            santriSelect.innerHTML = '<option value="">Pilih Santri</option>';
            if (!kelasOption || !kelasOption.value) return;
            const selectedId = santriSelect.dataset.selected;
            const santriList = JSON.parse(atob(kelasOption.dataset.santri || '') || '[]');
            santriList.forEach(function (s) {
                const option = document.createElement('option');
                option.value = s.id;
                option.textContent = s.name;
                if (selectedId && String(s.id) === String(selectedId)) {
                    option.selected = true;
                }
                santriSelect.appendChild(option);
            });
        }

        kelasSelect.addEventListener('change', function () {
            populateSantri(this.options[this.selectedIndex]);
        });

        if (kelasSelect.value) {
            populateSantri(kelasSelect.options[kelasSelect.selectedIndex]);
        }
    })();
</script>
@endsection
