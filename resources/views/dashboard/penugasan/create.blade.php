@extends('layouts.tenant')

@section('title', 'Tambah Penugasan Mengajar')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.akademik.penugasan.index', ['programSlug' => $programSlug]) }}" class="hover:text-emerald-600">Penugasan Mengajar</a>
    <i class="fa-solid fa-chevron-right text-[8px] mx-2"></i>
    <span class="text-gray-600 dark:text-gray-300">Tambah</span>
@endsection

@section('content')

<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ tenant_route('dashboard.akademik.penugasan.index', ['programSlug' => $programSlug]) }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke daftar
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">Tambah Penugasan Mengajar — {{ $program->name }}</h1>
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

    <form method="POST" action="{{ tenant_route('dashboard.akademik.penugasan.store', ['programSlug' => $programSlug]) }}"
          class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-5">
        @csrf

        {{-- Ustadz --}}
        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                Ustadz <span class="text-red-500">*</span>
            </label>
            @if($ustadz->isEmpty())
                <p class="text-[12px] text-amber-600 dark:text-amber-400 italic">
                    Belum ada ustadz. <a href="{{ tenant_route('dashboard.ustadz.create') }}" class="underline">Tambah ustadz</a> terlebih dahulu.
                </p>
            @else
                <select name="ustadz_id" required
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    <option value="">Pilih Ustadz</option>
                    @foreach($ustadz as $u)
                        <option value="{{ $u->id }}" {{ old('ustadz_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->user?->name ?? '-' }}
                        </option>
                    @endforeach
                </select>
            @endif
            @error('ustadz_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Kelas --}}
        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                Kelas <span class="text-red-500">*</span>
            </label>
            @if($kelasList->isEmpty())
                <p class="text-[12px] text-amber-600 dark:text-amber-400 italic">
                    Belum ada kelas untuk program {{ $program->name }}.
                    <a href="{{ tenant_route('dashboard.akademik.kelas.create', ['programSlug' => $programSlug]) }}" class="underline">Tambah kelas</a> terlebih dahulu.
                </p>
            @else
                <select name="kelas_id" id="kelas-select" required
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    <option value="" data-subjects="[]">Pilih Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" data-subjects='{{ json_encode($k->subjects->map(fn($s) => ["id" => $s->id, "name" => $s->name, "code" => $s->code]), JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) }}' {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->name }}
                        </option>
                    @endforeach
                </select>
            @endif
            @error('kelas_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Mata Pelajaran --}}
        <div>
            <label class="block text-[12px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                Mata Pelajaran <span class="text-red-500">*</span>
            </label>
            <select name="subject_id" id="subject-select" required
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                    disabled>
                <option value="">Pilih Kelas terlebih dahulu</option>
            </select>
            <p id="subject-empty-hint" class="text-[12px] text-amber-600 dark:text-amber-400 mt-1 hidden">
                Kelas ini belum memiliki mata pelajaran. 
                <a href="{{ tenant_route('dashboard.akademik.kelas.index', ['programSlug' => $programSlug]) }}" class="underline">Atur mapel per kelas</a>.
            </p>
            @error('subject_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        @push('scripts')
        <script>
            (function() {
                'use strict';
                
                const kelasSelect = document.getElementById('kelas-select');
                const subjectSelect = document.getElementById('subject-select');
                const subjectEmptyHint = document.getElementById('subject-empty-hint');
                
                console.log('[Penugasan] Script executed');
                console.log('[Penugasan] kelasSelect:', kelasSelect ? 'found' : 'NOT FOUND');
                console.log('[Penugasan] subjectSelect:', subjectSelect ? 'found' : 'NOT FOUND');
                console.log('[Penugasan] kelasSelect.value:', kelasSelect ? kelasSelect.value : 'N/A');
                
                if (!kelasSelect || !subjectSelect) {
                    console.error('[Penugasan] Required elements not found!');
                    return;
                }
                
                function updateSubjects() {
                    console.log('[Penugasan] updateSubjects() called');
                    console.log('[Penugasan] selectedIndex:', kelasSelect.selectedIndex);
                    
                    const selectedOption = kelasSelect.options[kelasSelect.selectedIndex];
                    if (!selectedOption) {
                        console.log('[Penugasan] No option at selectedIndex');
                        return;
                    }
                    
                    const subjectsData = selectedOption.getAttribute('data-subjects');
                    console.log('[Penugasan] data-subjects raw:', subjectsData);
                    
                    let subjects = [];
                    if (subjectsData) {
                        try {
                            subjects = JSON.parse(subjectsData);
                            console.log('[Penugasan] Parsed subjects:', subjects);
                        } catch (e) {
                            console.error('[Penugasan] JSON parse error:', e.message);
                            console.error('[Penugasan] Problematic data:', subjectsData);
                            subjects = [];
                        }
                    } else {
                        console.log('[Penugasan] No data-subjects attribute');
                    }
                    
                    // Clear existing options except placeholder
                    subjectSelect.innerHTML = '<option value="">Pilih Mata Pelajaran</option>';
                    
                    if (!Array.isArray(subjects) || subjects.length === 0) {
                        subjectSelect.disabled = true;
                        subjectEmptyHint.classList.remove('hidden');
                        console.log('[Penugasan] No subjects - showing empty hint');
                    } else {
                        subjectSelect.disabled = false;
                        subjectEmptyHint.classList.add('hidden');
                        
                        subjects.forEach(function(subject) {
                            const option = document.createElement('option');
                            option.value = subject.id;
                            option.textContent = subject.name + (subject.code ? ' (' + subject.code + ')' : '');
                            
                            // Pre-select if matches old value
                            const oldValue = {{ old('subject_id', 'null') }};
                            if (oldValue && subject.id == oldValue) {
                                option.selected = true;
                            }
                            
                            subjectSelect.appendChild(option);
                        });
                        
                        console.log('[Penugasan] Populated', subjects.length, 'subjects');
                    }
                }
                
                // Attach change event
                kelasSelect.addEventListener('change', function() {
                    console.log('[Penugasan] Change event - value:', this.value);
                    updateSubjects();
                });
                console.log('[Penugasan] Change listener attached');
                
                // Initial load if kelas already selected
                if (kelasSelect.value && kelasSelect.selectedIndex > 0) {
                    console.log('[Penugasan] Initial load for pre-selected kelas');
                    updateSubjects();
                } else {
                    console.log('[Penugasan] No pre-selected kelas (index:', kelasSelect.selectedIndex, ')');
                }
            })();
        </script>
        @endpush

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.akademik.penugasan.index', ['programSlug' => $programSlug]) }}"
               class="text-[13px] text-gray-500 hover:text-gray-700 dark:text-gray-400 transition-colors">Batal</a>
            <x-btn type="submit" variant="primary" icon="fa-floppy-disk">Simpan</x-btn>
        </div>
    </form>
</div>

@endsection
