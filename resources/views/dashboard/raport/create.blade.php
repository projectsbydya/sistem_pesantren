@extends('layouts.tenant')

@section('title', strtoupper($programSlug) . ' — Buat E-Raport')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Buat E-Raport {{ strtoupper($programSlug) }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih santri dan semester untuk generate raport</p>
        </div>
        <a href="{{ tenant_route('dashboard.akademik.raport.index', ['programSlug' => $programSlug]) }}"
           class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ tenant_route('dashboard.akademik.raport.generate', ['programSlug' => $programSlug]) }}"
          class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kelas</label>
                <select id="kelas_id" name="kelas_id" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    <option value="">Pilih Kelas</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" data-santri="{{ base64_encode(json_encode($kelas->santri->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values())) }}">
                            {{ $kelas->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Santri</label>
                <select id="santri_id" name="santri_id" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    <option value="">Pilih Santri</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Semester</label>
                <select name="semester" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    <option value="ganjil" {{ $semester === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="genap" {{ $semester === 'genap' ? 'selected' : '' }}>Genap</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm"
                       placeholder="2025/2026">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Hari Efektif</label>
                <input type="number" name="total_hari_efektif" value="0" required min="1"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.akademik.raport.index', ['programSlug' => $programSlug]) }}"
               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                Generate Raport
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('kelas_id').addEventListener('change', function () {
        const santriSelect = document.getElementById('santri_id');
        santriSelect.innerHTML = '<option value="">Pilih Santri</option>';
        const selected = this.options[this.selectedIndex];
        const santriList = JSON.parse(atob(selected.dataset.santri || '') || '[]');
        santriList.forEach(function (s) {
            const option = document.createElement('option');
            option.value = s.id;
            option.textContent = s.name;
            santriSelect.appendChild(option);
        });
    });
</script>
@endsection
