@extends('layouts.tenant')

@section('title', 'Tambah Materi')
@section('page-title', 'Tambah Materi')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.akademik.materi.index', ['programSlug' => $programSlug]) }}" class="text-emerald-600 hover:text-emerald-700">Materi</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Tambah</span>
@endsection

@section('content')

{{-- Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tambah Materi Pembelajaran</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        {{ $program->name }}
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <form action="{{ tenant_route('dashboard.akademik.materi.store', ['programSlug' => $programSlug]) }}" method="POST" class="p-6 space-y-6">
        @csrf

        @if(isset($jadwal))
            <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
            <input type="hidden" name="ustadz_kelas_id" value="{{ $jadwal->ustadz_kelas_id }}">
            <input type="hidden" name="kelas_id" value="{{ $jadwal->kelas_id }}">
            <input type="hidden" name="subject_id" value="{{ $jadwal->ustadzKelas?->subject_id }}">

            {{-- Schedule Info --}}
            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4">
                <h3 class="text-sm font-medium text-indigo-900 dark:text-indigo-300 mb-2">Informasi Jadwal</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-[13px]">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Hari:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-1">{{ $jadwal->hari }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Waktu:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-1">{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Kelas:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-1">{{ $jadwal->ustadzKelas?->kelas?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Mata Pelajaran:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-1">{{ $jadwal->ustadzKelas?->subject?->name ?? $jadwal->mata_pelajaran }}</span>
                    </div>
                </div>
            </div>
        @else
            {{-- Jadwal Selection --}}
            <div>
                <label for="jadwal_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jadwal (Opsional)</label>
                <select name="jadwal_id" id="jadwal_id"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Tanpa Jadwal --</option>
                    @foreach($jadwalList ?? [] as $j)
                        <option value="{{ $j->id }}" data-kelas="{{ $j->kelas_id }}" data-subject="{{ $j->ustadzKelas?->subject_id }}" data-ustadz="{{ $j->ustadz_kelas_id }}">
                            {{ $j->hari }} {{ substr($j->jam_mulai, 0, 5) }} - {{ $j->ustadzKelas?->kelas?->name ?? $j->kelas }} ({{ $j->ustadzKelas?->subject?->name ?? $j->mata_pelajaran }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px] text-gray-500">Pilih jadwal untuk mengaitkan materi dengan jadwal tertentu</p>
            </div>

            {{-- Ustadz Kelas --}}
            <div>
                <label for="ustadz_kelas_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Penugasan Ustadz <span class="text-red-500">*</span></label>
                <select name="ustadz_kelas_id" id="ustadz_kelas_id" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Pilih Penugasan --</option>
                    @foreach($ustadzKelas ?? [] as $uk)
                        <option value="{{ $uk->id }}" data-kelas="{{ $uk->kelas_id }}" data-subject="{{ $uk->subject_id }}">
                            {{ $uk->ustadz?->user?->name ?? 'Ustadz' }} - {{ $uk->kelas?->name ?? 'Kelas' }} ({{ $uk->subject?->name ?? 'Mata Pelajaran' }})
                        </option>
                    @endforeach
                </select>
                @error('ustadz_kelas_id')
                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kelas --}}
            <div>
                <label for="kelas_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                <select name="kelas_id" id="kelas_id" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Pilih Kelas --</option>
                </select>
                @error('kelas_id')
                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Subject --}}
            <div>
                <label for="subject_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                <select name="subject_id" id="subject_id" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                </select>
                @error('subject_id')
                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Tanggal --}}
        <div>
            <label for="tanggal" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" id="tanggal" value="{{ $tanggal ?? today()->toDateString() }}" required
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500">
            @error('tanggal')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Judul --}}
        <div>
            <label for="judul" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Judul Materi <span class="text-red-500">*</span></label>
            <input type="text" name="judul" id="judul" value="{{ old('judul') }}" required maxlength="255"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500"
                   placeholder="Contoh: Bab 1 - Pendahuluan">
            @error('judul')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Deskripsi --}}
        <div>
            <label for="deskripsi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" rows="3"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500"
                      placeholder="Deskripsi singkat materi pembelajaran...">{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tujuan Pembelajaran --}}
        <div>
            <label for="tujuan_pembelajaran" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tujuan Pembelajaran</label>
            <textarea name="tujuan_pembelajaran" id="tujuan_pembelajaran" rows="3"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500"
                      placeholder="Setelah pembelajaran ini, santri mampu...">{{ old('tujuan_pembelajaran') }}</textarea>
            @error('tujuan_pembelajaran')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Aktivitas --}}
        <div>
            <label for="aktivitas" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Aktivitas/Kegiatan</label>
            <textarea name="aktivitas" id="aktivitas" rows="4"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500"
                      placeholder="1. Pembukaan...
2. Kegiatan inti...
3. Penutup...">{{ old('aktivitas') }}</textarea>
            @error('aktivitas')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Referensi --}}
        <div>
            <label for="referensi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Referensi/Bahan Bacaan</label>
            <textarea name="referensi" id="referensi" rows="2"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500"
                      placeholder="Buku, artikel, atau sumber referensi lainnya...">{{ old('referensi') }}</textarea>
            @error('referensi')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status --}}
        <div>
            <label for="status" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status <span class="text-red-500">*</span></label>
            <select name="status" id="status" required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-[13px] focus:ring-emerald-500 focus:border-emerald-500">
                @foreach(\App\Models\Materi::STATUS as $status)
                    <option value="{{ $status }}" {{ old('status', 'draft') === $status ? 'selected' : '' }}>
                        {{ \App\Models\Materi::STATUS_LABELS[$status] }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
            <x-btn href="{{ tenant_route('dashboard.akademik.materi.index', ['programSlug' => $programSlug]) }}" variant="outline">
                Batal
            </x-btn>
            <x-btn type="submit" variant="primary">
                Simpan Materi
            </x-btn>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jadwalSelect = document.getElementById('jadwal_id');
    const ustadzKelasSelect = document.getElementById('ustadz_kelas_id');
    const kelasSelect = document.getElementById('kelas_id');
    const subjectSelect = document.getElementById('subject_id');

    function updateSelections(source) {
        let kelasId, subjectId, ustadzKelasId;

        if (source === 'jadwal' && jadwalSelect.value) {
            const option = jadwalSelect.options[jadwalSelect.selectedIndex];
            kelasId = option.dataset.kelas;
            subjectId = option.dataset.subject;
            ustadzKelasId = option.dataset.ustadz;

            // Sync ustadz kelas
            if (ustadzKelasSelect) {
                ustadzKelasSelect.value = ustadzKelasId;
            }
        } else if (source === 'ustadz' && ustadzKelasSelect.value) {
            const option = ustadzKelasSelect.options[ustadzKelasSelect.selectedIndex];
            kelasId = option.dataset.kelas;
            subjectId = option.dataset.subject;
        }

        // Update kelas select
        if (kelasSelect && kelasId) {
            kelasSelect.value = kelasId;
        }

        // Update subject select
        if (subjectSelect && subjectId) {
            subjectSelect.value = subjectId;
        }
    }

    if (jadwalSelect) {
        jadwalSelect.addEventListener('change', () => updateSelections('jadwal'));
    }

    if (ustadzKelasSelect) {
        ustadzKelasSelect.addEventListener('change', () => updateSelections('ustadz'));
    }
});
</script>
@endpush

@endsection
