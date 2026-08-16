@extends('layouts.tenant')

@section('title', 'Target Hafalan')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Target Hafalan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Target hafalan Quran & Kitab untuk santri</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 p-4 text-sm text-emerald-700 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Quick Add --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Tambah Target</h3>
        <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.target-hafalan.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
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
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tipe</label>
                <select name="type" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @foreach(\App\Models\TargetHafalan::TYPES as $t)
                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Target</label>
                <input type="text" name="target" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm" placeholder="Juz 30 / Kitab Jurumiyah">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Deadline</label>
                <input type="date" name="deadline" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @foreach(\App\Models\TargetHafalan::STATUS as $st)
                        <option value="{{ $st }}">{{ \App\Models\TargetHafalan::STATUS_LABELS[$st] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
                    <i class="fa-solid fa-plus mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>

    @if($targets->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-bullseye text-gray-400 dark:text-gray-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada target hafalan</h3>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Target</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Deadline</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($targets as $tgt)
                        @php $color = \App\Models\TargetHafalan::STATUS_COLORS[$tgt->status] ?? 'gray'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tgt->santri?->name }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($tgt->type) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $tgt->target }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $tgt->deadline?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 text-{{ $color }}-700 dark:text-{{ $color }}-400">
                                    {{ \App\Models\TargetHafalan::STATUS_LABELS[$tgt->status] }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                @if($tgt->status !== 'tercapai')
                                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.target-hafalan.update', ['id' => $tgt->id]) }}" class="inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="tercapai">
                                        <button type="submit" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 text-xs font-semibold">Tercapai</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
