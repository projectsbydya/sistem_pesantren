@extends('layouts.tenant')

@section('title', 'Nilai Keagamaan - ' . $program->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Nilai Keagamaan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $program->name }} &mdash; Penilaian keagamaan santri</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Tambah Record</h3>
        <form method="POST" action="{{ tenant_route('dashboard.diniyah.nilai-keagamaan.store', ['programSlug' => $programSlug]) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Santri</label>
                <select name="santri_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="">Pilih...</option>
                    @foreach($santriList as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis Penilaian</label>
                <input type="text" name="aspect" required maxlength="100" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm" placeholder="Ujian Tengah Semester...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Periode</label>
                <input type="text" name="notes" maxlength="500" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm" placeholder="Catatan tambahan...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nilai (0-100)</label>
                <input type="number" name="score" min="0" max="100" step="0.1" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
                    <i class="fa-solid fa-plus mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>

    @if($records->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-graduation-cap text-gray-400 dark:text-gray-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada data nilai keagamaan</h3>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Jenis Penilaian</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Periode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nilai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Predikat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($records as $rec)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $rec->created_at?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rec->santri?->name }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $rec->aspect }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $rec->notes ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($rec->score, 1) }}</td>
                            <td class="px-4 py-2">
                                @php
                                    $pred = $rec->predikat ?? \App\Models\DiniyahAssessment::hitungPredikat($rec->score);
                                    $predColor = match($pred) { 'A' => 'emerald', 'B' => 'blue', 'C' => 'amber', default => 'red' };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $predColor }}-100 dark:bg-{{ $predColor }}-500/20 text-{{ $predColor }}-700 dark:text-{{ $predColor }}-400">
                                    {{ $pred }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ tenant_route('dashboard.diniyah.nilai-keagamaan.destroy', ['programSlug' => $programSlug, 'id' => $rec->id]) }}" onsubmit="return confirm('Hapus record ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
