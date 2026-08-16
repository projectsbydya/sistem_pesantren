@extends('layouts.tenant')
@section('title', 'Tambah Jadwal Live Pengajian')
@section('content')
<div class="max-w-2xl mx-auto">

<div class="mb-6 flex items-center gap-3">
    <a href="{{ tenant_route('dashboard.live-pengajian.index') }}"
       class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Jadwal Live</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Jadwalkan sesi live pengajian baru</p>
    </div>
</div>

<x-card>
    @if($errors->any())
        <x-alert type="error" class="mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ tenant_route('dashboard.live-pengajian.store') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Judul Pengajian <span class="text-red-500">*</span>
            </label>
            <input type="text" name="judul" value="{{ old('judul') }}" required
                   class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                   placeholder="Contoh: Kajian Fiqih Bab Shalat — Ust. Ahmad">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
            <textarea name="deskripsi" rows="3"
                      class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-none"
                      placeholder="Deskripsi singkat tentang materi atau ustadz">{{ old('deskripsi') }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Platform <span class="text-red-500">*</span>
                </label>
                <select name="platform" required id="platform-select"
                        class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    <option value="youtube" {{ old('platform', 'youtube') === 'youtube' ? 'selected' : '' }}>🔴 YouTube Live</option>
                    <option value="zoom" {{ old('platform') === 'zoom' ? 'selected' : '' }}>🔵 Zoom</option>
                    <option value="gmeet" {{ old('platform') === 'gmeet' ? 'selected' : '' }}>🟢 Google Meet</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Status
                </label>
                <select name="status"
                        class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    <option value="scheduled" {{ old('status', 'scheduled') === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                    <option value="live" {{ old('status') === 'live' ? 'selected' : '' }}>Sedang Live</option>
                    <option value="ended" {{ old('status') === 'ended' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Link URL <span class="text-red-500">*</span>
            </label>
            <input type="url" name="link_url" value="{{ old('link_url') }}" required
                   class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                   placeholder="https://youtube.com/live/... atau https://zoom.us/j/... atau https://meet.google.com/...">
            <p class="text-xs text-gray-400 mt-1">URL lengkap untuk bergabung ke sesi live</p>
        </div>

        {{-- Zoom/Meet specific fields --}}
        <div id="zoom-fields" class="{{ old('platform', 'youtube') === 'youtube' ? 'hidden' : '' }} grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Meeting ID</label>
                <input type="text" name="meeting_id" value="{{ old('meeting_id') }}"
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                       placeholder="123 456 7890">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Passcode</label>
                <input type="text" name="passcode" value="{{ old('passcode') }}"
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                       placeholder="Kode akses (opsional)">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Jadwal Mulai <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="jadwal_mulai" value="{{ old('jadwal_mulai') }}" required
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jadwal Selesai</label>
                <input type="datetime-local" name="jadwal_selesai" value="{{ old('jadwal_selesai') }}"
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.live-pengajian.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-save mr-1.5"></i> Simpan Jadwal
            </button>
        </div>
    </form>
</x-card>

</div>
<script>
document.getElementById('platform-select').addEventListener('change', function () {
    const zoomFields = document.getElementById('zoom-fields');
    zoomFields.classList.toggle('hidden', this.value === 'youtube');
});
</script>

@endsection
