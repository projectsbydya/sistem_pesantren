@extends('layouts.tenant')

@section('title', 'Konfigurasi Penilaian ' . $program->name)
@section('page-title', 'Konfigurasi Penilaian')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">{{ $program->name }}</span>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Konfigurasi Penilaian</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $program->name }}</p>
    </div>
</div>

@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif

@if($errors->any())
    <x-alert type="error" class="mb-5">{{ $errors->first() }}</x-alert>
@endif

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    @if($configs->isEmpty())
        <div class="text-center py-14 px-6">
            <i class="fa-solid fa-clipboard-list text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
            <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada konfigurasi penilaian</p>
        </div>
    @else
        @foreach($configs as $config)
            <form id="assessment-config-{{ $config->id }}" method="POST" action="{{ tenant_route('dashboard.akademik.assessment-config.update', ['programSlug' => $programSlug, 'config' => $config->id]) }}">
                @csrf
                @method('PATCH')
            </form>
        @endforeach
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Penilaian</th>
                        <th class="text-center px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aktif</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bobot (%)</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Urutan</th>
                        <th class="text-right px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($configs as $config)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $config->assessmentType->label }}</div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $config->assessmentType->code }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input form="assessment-config-{{ $config->id }}" type="hidden" name="is_active" value="0">
                                <input form="assessment-config-{{ $config->id }}" type="checkbox" name="is_active" value="1" @checked($config->is_active)
                                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            </td>
                            <td class="px-4 py-3">
                                <input form="assessment-config-{{ $config->id }}" type="number" name="weight" value="{{ old('weight', $config->weight) }}" min="0" max="100" step="0.01" placeholder="—"
                                       class="w-24 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </td>
                            <td class="px-4 py-3">
                                <input form="assessment-config-{{ $config->id }}" type="number" name="sort_order" value="{{ old('sort_order', $config->sort_order) }}" min="0" step="1"
                                       class="w-20 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button form="assessment-config-{{ $config->id }}" type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                                    Simpan
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
