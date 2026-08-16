@extends('layouts.tenant')

@section('title', 'E-Learning — ' . strtoupper($programSlug))

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">E-Learning {{ strtoupper($programSlug) }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Materi pembelajaran digital</p>
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->isUstadz())
            <a href="{{ tenant_route('dashboard.akademik.elearning.create', ['programSlug' => $programSlug]) }}"
               class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Materi
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 p-4 text-sm text-emerald-700 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if($materials->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-photo-film text-gray-400 dark:text-gray-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada materi</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tambahkan materi e-learning untuk memulai.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($materials as $m)
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-md transition-shadow">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $m->judul }}</h3>
                    @if($m->deskripsi)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-3">{{ $m->deskripsi }}</p>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500 mb-3">
                        <span>{{ $m->ustadz?->user?->name ?? '-' }}</span>
                        @if($m->subject) <span>· {{ $m->subject->name }}</span> @endif
                        @if($m->kelas) <span>· {{ $m->kelas->name }}</span> @endif
                    </div>
                    @if($m->link)
                        <a href="{{ $m->link }}" target="_blank" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 text-sm font-medium">
                            <i class="fa-solid fa-external-link mr-1"></i> Buka Link
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
