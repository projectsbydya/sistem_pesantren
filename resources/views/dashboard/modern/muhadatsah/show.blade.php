@extends('layouts.tenant')

@php
    $isArabic  = $type === 'arabic';
    $typeLabel = \App\Models\Muhadatsah::getLabels()[$type] ?? 'Muhadatsah';
    $icon      = $isArabic ? 'fa-comments' : 'fa-comment-dots';
    $accentBg  = $isArabic ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-blue-50 dark:bg-blue-500/10';
    $accentTxt = $isArabic ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400';
@endphp

@section('title', $typeLabel . ' — ' . $santri->name)
@section('page-title', $typeLabel)
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $type]) }}"
       class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">{{ $typeLabel }}</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">{{ $santri->name }}</span>
@endsection

@section('content')

{{-- Header --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $type]) }}"
       class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
        <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div class="w-10 h-10 rounded-full {{ $accentBg }} {{ $accentTxt }} flex items-center justify-center text-[14px] font-bold shrink-0">
        {{ strtoupper(substr($santri->name, 0, 1)) }}
    </div>
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $typeLabel }} — {{ $santri->name }}</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $program->name }}</p>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <x-stat-card title="{{ $isArabic ? 'Total Muhadatsah' : 'Total Conversation' }}" value="{{ $records->count() }}" icon="{{ $icon }}" color="{{ $isArabic ? 'emerald' : 'blue' }}" />
    <x-stat-card title="Rata-rata Nilai" value="{{ $records->whereNotNull('score')->avg('score') !== null ? number_format($records->whereNotNull('score')->avg('score'), 1) : '—' }}" icon="fa-star-half-stroke" color="amber" />
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    @if($records->isEmpty())
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full {{ $accentBg }} flex items-center justify-center">
                <i class="fa-solid {{ $icon }} {{ $accentTxt }} text-2xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">
                Belum ada {{ $isArabic ? 'muhadatsah' : 'conversation' }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                {{ $isArabic ? 'Belum ada sesi muhadatsah yang direkam untuk ' . $santri->name . '.' : 'No conversation sessions recorded for ' . $santri->name . ' yet.' }}
            </p>
            <x-btn href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $type]) }}"
                   variant="primary" icon="fa-arrow-left" size="sm">
                Kembali ke Daftar
            </x-btn>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <span class="flex items-center gap-1.5"><i class="fa-solid {{ $icon }} text-[10px]"></i>{{ $isArabic ? 'Topik' : 'Topic' }}</span>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ $isArabic ? 'Isi / Percakapan' : 'Content' }}
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nilai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dicatat</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $rec)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="font-semibold text-gray-900 dark:text-gray-100 {{ $isArabic ? 'text-base leading-relaxed' : 'text-[13px]' }}"
                                  dir="{{ $isArabic ? 'rtl' : 'ltr' }}">{{ $rec->topic }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($rec->category)
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">{{ $rec->category }}</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600 text-[12px]">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 max-w-[220px]">
                            <span class="text-[12px] text-gray-400 dark:text-gray-500 italic truncate block"
                                  dir="{{ $isArabic ? 'rtl' : 'ltr' }}" title="{{ $rec->content }}">
                                {{ $rec->content ? Str::limit($rec->content, 55) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[13px] font-bold text-gray-700 dark:text-gray-300">{{ $rec->score !== null ? $rec->score : '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-400 dark:text-gray-500">{{ $rec->created_at?->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                @can('update', $rec)
                                    <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.edit', ['programSlug' => $programSlug, 'id' => $rec->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $rec)
                                    <form method="POST"
                                          action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.destroy', ['programSlug' => $programSlug, 'id' => $rec->id]) }}"
                                          onsubmit="return confirm('Hapus data muhadatsah ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
