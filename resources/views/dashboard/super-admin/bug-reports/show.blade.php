@extends('layouts.dashboard')

@section('title', 'Detail Laporan Bug — ' . $bugReport->title)
@section('page-title', 'Detail Laporan Bug')

@php
    $severityVariant = match ($bugReport->severity) {
        \App\Models\BugReport::SEVERITY_CRITICAL, \App\Models\BugReport::SEVERITY_HIGH => 'danger',
        \App\Models\BugReport::SEVERITY_MEDIUM => 'warning',
        \App\Models\BugReport::SEVERITY_LOW => 'success',
        default => 'default',
    };

    $statusVariant = match ($bugReport->status) {
        \App\Models\BugReport::STATUS_OPEN => 'info',
        \App\Models\BugReport::STATUS_IN_PROGRESS => 'warning',
        \App\Models\BugReport::STATUS_RESOLVED => 'success',
        \App\Models\BugReport::STATUS_CLOSED => 'default',
        default => 'default',
    };

    $categoryLabels = [
        \App\Models\BugReport::CATEGORY_BUG => 'Bug',
        \App\Models\BugReport::CATEGORY_ERROR => 'Error',
        \App\Models\BugReport::CATEGORY_FEATURE_REQUEST => 'Permintaan Fitur',
        \App\Models\BugReport::CATEGORY_SUPPORT => 'Dukungan',
    ];

    $statusLabels = [
        \App\Models\BugReport::STATUS_OPEN => 'Terbuka',
        \App\Models\BugReport::STATUS_IN_PROGRESS => 'Diproses',
        \App\Models\BugReport::STATUS_RESOLVED => 'Selesai',
        \App\Models\BugReport::STATUS_CLOSED => 'Ditutup',
    ];

    $severityLabels = [
        \App\Models\BugReport::SEVERITY_LOW => 'Rendah',
        \App\Models\BugReport::SEVERITY_MEDIUM => 'Sedang',
        \App\Models\BugReport::SEVERITY_HIGH => 'Tinggi',
        \App\Models\BugReport::SEVERITY_CRITICAL => 'Kritis',
    ];
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="{{ $bugReport->title }}"
        subtitle="Dari {{ $bugReport->reporter?->name ?? 'Tidak diketahui' }} — {{ $bugReport->tenant?->name ?? 'Tenant tidak diketahui' }}"
        backUrl="{{ route('dashboard.super-admin.index') }}">
        <x-slot:actions>
            <x-badge variant="{{ $statusVariant }}" size="md">
                {{ $statusLabels[$bugReport->status] ?? ucfirst($bugReport->status) }}
            </x-badge>
            <x-badge variant="{{ $severityVariant }}" size="md">
                {{ $severityLabels[$bugReport->severity] ?? ucfirst($bugReport->severity) }}
            </x-badge>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Main report details -->
        <x-card class="lg:col-span-2" title="Deskripsi Laporan" subtitle="ID #{{ $bugReport->id }}">
            <div class="prose prose-sm max-w-none text-gray-700">
                {{ $bugReport->description }}
            </div>

            @if($bugReport->source_url)
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <span class="text-sm text-gray-500">Sumber URL:</span>
                    <a href="{{ $bugReport->source_url }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="ml-2 text-sm text-blue-600 hover:text-blue-800 hover:underline break-all">
                        {{ $bugReport->source_url }}
                    </a>
                </div>
            @endif
        </x-card>

        <!-- Metadata sidebar -->
        <x-card title="Informasi" class="lg:col-span-1">
            <dl class="space-y-4 text-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-gray-500">Kategori</dt>
                    <dd class="font-medium text-gray-900">{{ $categoryLabels[$bugReport->category] ?? ucfirst($bugReport->category) }}</dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-gray-500">Severity</dt>
                    <dd>
                        <x-badge variant="{{ $severityVariant }}" size="sm">
                            {{ $severityLabels[$bugReport->severity] ?? ucfirst($bugReport->severity) }}
                        </x-badge>
                    </dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <x-badge variant="{{ $statusVariant }}" size="sm">
                            {{ $statusLabels[$bugReport->status] ?? ucfirst($bugReport->status) }}
                        </x-badge>
                    </dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-gray-500">Reporter</dt>
                    <dd class="font-medium text-gray-900">{{ $bugReport->reporter?->name ?? 'Tidak diketahui' }}</dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-gray-500">Tenant</dt>
                    <dd class="font-medium text-gray-900">{{ $bugReport->tenant?->name ?? 'Tidak diketahui' }}</dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-gray-500">Dibuat</dt>
                    <dd class="text-gray-900">{{ $bugReport->created_at?->format('d M Y H:i') ?? '-' }}</dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <dt class="text-gray-500">Diperbarui</dt>
                    <dd class="text-gray-900">{{ $bugReport->updated_at?->format('d M Y H:i') ?? '-' }}</dd>
                </div>
            </dl>
        </x-card>
    </div>

    <!-- Attachments -->
    <x-card title="Screenshot / Lampiran" subtitle="{{ $bugReport->attachments->count() }} file">
        @if($bugReport->attachments->isEmpty())
            <div class="text-center py-10">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-100 mb-3">
                    <i class="fa-regular fa-image text-2xl text-gray-400"></i>
                </div>
                <p class="text-sm text-gray-500">Tidak ada screenshot atau lampiran untuk laporan ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($bugReport->attachments as $attachment)
                    @php
                        $isImage = \Illuminate\Support\Str::startsWith($attachment->mime_type, 'image/');
                        $fileUrl = $attachment->url();
                    @endphp

                    <div class="group rounded-xl border border-gray-200 bg-white overflow-hidden hover:border-gray-300 transition-colors">
                        @if($isImage)
                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                                <div class="aspect-video bg-gray-50 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $fileUrl }}"
                                         alt="{{ $attachment->original_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         loading="lazy">
                                </div>
                            </a>
                        @else
                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                                <div class="aspect-video bg-gray-50 flex flex-col items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-file text-3xl mb-2"></i>
                                    <span class="text-xs">Buka file</span>
                                </div>
                            </a>
                        @endif

                        <div class="p-3 border-t border-gray-100">
                            <p class="text-sm font-medium text-gray-900 truncate" title="{{ $attachment->original_name }}">
                                {{ $attachment->original_name }}
                            </p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500">{{ strtoupper(pathinfo($attachment->original_name, PATHINFO_EXTENSION)) }}</span>
                                <span class="text-xs text-gray-500">{{ \Illuminate\Support\Number::fileSize($attachment->size) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
</div>
@endsection
