@extends('layouts.tenant')

@section('title', 'Edit Sesi Kelas')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.akademik.class-sessions.index', ['programSlug' => $programSlug]) }}" class="hover:text-emerald-600">Sesi Kelas</a>
    <i class="fa-solid fa-chevron-right text-[8px] mx-2"></i>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')

<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ tenant_route('dashboard.akademik.class-sessions.index', ['programSlug' => $programSlug]) }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke daftar
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">Edit Sesi Kelas — {{ $program->name }}</h1>
    </div>

    @if($errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-5">{{ session('error') }}</x-alert>
    @endif

    <form method="POST" action="{{ tenant_route('dashboard.akademik.class-sessions.update', ['programSlug' => $programSlug, 'id' => $session->id]) }}"
          class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-5">
        @csrf @method('PUT')

        {{-- Jadwal --}}
        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                Jadwal <span class="text-red-500">*</span>
            </label>
            <select name="schedule_id" required
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                @foreach($schedules as $j)
                    <option value="{{ $j->id }}" {{ old('schedule_id', $session->schedule_id) == $j->id ? 'selected' : '' }}>
                        {{ $j->hari }} · {{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}
                        — {{ $j->ustadzKelas?->kelas?->name ?? $j->kelas ?? '-' }}
                        — {{ $j->ustadzKelas?->subject?->name ?? $j->mata_pelajaran ?? '-' }}
                        ({{ $j->ustadzKelas?->ustadz?->user?->name ?? '-' }})
                    </option>
                @endforeach
            </select>
            @error('schedule_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Tanggal Sesi --}}
        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                Tanggal Sesi <span class="text-red-500">*</span>
            </label>
            <input type="date" name="session_date" value="{{ old('session_date', $session->session_date?->format('Y-m-d')) }}" required
                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            @error('session_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                Status <span class="text-red-500">*</span>
            </label>
            <select name="status" required
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                @foreach(\App\Models\ClassSession::STATUS as $st)
                    <option value="{{ $st }}" {{ old('status', $session->status) === $st ? 'selected' : '' }}>
                        {{ \App\Models\ClassSession::STATUS_LABELS[$st] }}
                    </option>
                @endforeach
            </select>
            @error('status')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Waktu Mulai & Selesai --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Waktu Mulai</label>
                <input type="datetime-local" name="started_at" value="{{ old('started_at', $session->started_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                @error('started_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Waktu Selesai</label>
                <input type="datetime-local" name="ended_at" value="{{ old('ended_at', $session->ended_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                @error('ended_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Catatan --}}
        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Catatan</label>
            <textarea name="notes" rows="3"
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">{{ old('notes', $session->notes) }}</textarea>
            @error('notes')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="pt-2 flex justify-end gap-3">
            <a href="{{ tenant_route('dashboard.akademik.class-sessions.index', ['programSlug' => $programSlug]) }}"
               class="px-4 py-2 text-[13px] font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-medium rounded-lg transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@endsection
