@extends('layouts.tenant')

@section('title', strtoupper($programSlug) . ' — Edit E-Raport')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit E-Raport</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $raport->santri?->name ?? '-' }} — {{ $raport->kelas?->name ?? '-' }} — {{ ucfirst($raport->semester) }} {{ $raport->tahun_ajaran }}
            </p>
        </div>
        <a href="{{ tenant_route('dashboard.akademik.raport.show', ['programSlug' => $programSlug, 'id' => $raport->id]) }}"
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

    <form method="POST" action="{{ tenant_route('dashboard.akademik.raport.update', ['programSlug' => $programSlug, 'id' => $raport->id]) }}"
          class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Nilai per subject --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Nilai Akademik</h2>
            @if($raport->nilaiRaport->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data nilai.</p>
            @else
                @php
                    $componentCodes = collect();
                    $componentLabels = [];
                    foreach ($raport->nilaiRaport as $nilai) {
                        foreach ($nilai->nilaiComponents as $component) {
                            if (! $componentCodes->contains($component->assessment_code)) {
                                $componentCodes->push($component->assessment_code);
                                $componentLabels[$component->assessment_code] = $component->assessment_label;
                            }
                        }
                    }
                @endphp
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Mata Pelajaran</th>
                                @foreach($componentCodes as $code)
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $componentLabels[$code] ?? $code }}</th>
                                @endforeach
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">Akhir</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">Predikat</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($raport->nilaiRaport as $nilai)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $nilai->subject?->name ?? '-' }}</td>
                                    @foreach($componentCodes as $code)
                                        @php
                                            $component = $nilai->nilaiComponents->firstWhere('assessment_code', $code);
                                        @endphp
                                        <td class="px-3 py-2 text-sm text-center text-gray-600 dark:text-gray-400">{{ $component?->score ?? '-' }}</td>
                                    @endforeach
                                    <td class="px-3 py-2 text-sm text-center font-semibold text-gray-900 dark:text-gray-100">{{ $nilai->nilai_akhir }}</td>
                                    <td class="px-3 py-2 text-sm text-center text-gray-900 dark:text-gray-100">{{ $nilai->predikat }}</td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="nilai[{{ $nilai->id }}][deskripsi]" value="{{ $nilai->deskripsi }}"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Raport header fields --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informasi Tambahan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Umum</label>
                    <textarea name="catatan_umum" rows="3"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">{{ $raport->catatan_umum }}</textarea>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sakit</label>
                        <input type="number" name="sakit" value="{{ $raport->sakit }}" min="0"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Izin</label>
                        <input type="number" name="izin" value="{{ $raport->izin }}" min="0"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alpa</label>
                        <input type="number" name="alpa" value="{{ $raport->alpa }}" min="0"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ tenant_route('dashboard.akademik.raport.show', ['programSlug' => $programSlug, 'id' => $raport->id]) }}"
               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
