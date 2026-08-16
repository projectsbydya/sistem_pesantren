@extends('layouts.tenant')

@section('title', 'Tambah Kelas')
@section('page-title', 'Tambah Kelas')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.akademik.kelas.index', ['programSlug' => $programSlug]) }}" class="hover:text-emerald-600">Kelas {{ strtoupper($programSlug) }}</a>
    <i class="fa-solid fa-chevron-right text-[8px] mx-2"></i>
    <span class="text-gray-600 dark:text-gray-300">Tambah</span>
@endsection

@section('content')

<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ tenant_route('dashboard.akademik.kelas.index', ['programSlug' => $programSlug]) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke daftar
        </a>
    </div>
    @if($errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ tenant_route('dashboard.akademik.kelas.store', ['programSlug' => $programSlug]) }}"
          class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Nama Kelas <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
            <textarea name="description" rows="2"
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-none">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2">Mata Pelajaran</label>
            @if($subjects->isEmpty())
                <p class="text-[12px] text-gray-400 dark:text-gray-500 italic">Belum ada mata pelajaran. Fitur ini sedang dalam pengembangan.</p>
            @else
                <div class="grid grid-cols-2 gap-2">
                    @foreach($subjects as $s)
                        <label class="flex items-center gap-2 text-[13px] text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="subject_ids[]" value="{{ $s->id }}"
                                   {{ in_array($s->id, old('subject_ids', [])) ? 'checked' : '' }}
                                   class="w-4 h-4 accent-emerald-600 cursor-pointer">
                            {{ $s->name }}
                            @if($s->code)
                                <span class="text-[10px] text-gray-400 font-mono">({{ $s->code }})</span>
                            @endif
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.akademik.kelas.index', ['programSlug' => $programSlug]) }}" class="text-[13px] text-gray-500 hover:text-gray-700 dark:text-gray-400 transition-colors">Batal</a>
            <x-btn type="submit" variant="primary" icon="fa-floppy-disk">Simpan</x-btn>
        </div>
    </form>
</div>

@endsection
