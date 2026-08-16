@extends('layouts.tenant')

@section('title', 'Pilih Program - Setup Pesantren')

@section('content')
<div class="min-h-screen p-4">
    <div class="w-full max-w-4xl mx-auto">
        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Progress Setup</span>
                <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Langkah 1 dari 2</span>
            </div>
            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: 50%"></div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            {{-- Header --}}
            <div class="p-8 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i class="fa-solid fa-layer-group text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Pilih Program</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Program yang tersedia untuk pesantren Anda</p>
                    </div>
                </div>
                
                <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                <strong>Apa itu Program?</strong> Program adalah jenjang atau tipe pembelajaran 
                                yang tersedia secara global. Anda dapat memilih satu atau beberapa program 
                                sesuai dengan struktur pesantren Anda.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('dashboard.onboarding.programs.store') }}" class="p-8">
                @csrf

                @if($errors->has('programs'))
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-red-600 dark:text-red-400"></i>
                        <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first('programs') }}</p>
                    </div>
                @endif

                {{-- Program Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    @forelse($availablePrograms as $program)
                        <label class="relative cursor-pointer group">
                            <input type="checkbox" name="programs[]" value="{{ $program->id }}" 
                                   class="peer sr-only"
                                   {{ in_array($program->id, $selectedProgramIds) ? 'checked' : '' }}>
                            
                            <div class="p-5 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 dark:peer-checked:bg-emerald-500/10 hover:border-emerald-300 dark:hover:border-emerald-400 transition-all">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-book-open text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $program->name }}</h3>
                                            <i class="fa-solid fa-check-circle text-emerald-600 dark:text-emerald-400 opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                        </div>
                                        <div class="description-container" data-program-id="{{ $program->id }}">
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 description-text line-clamp-2">
                                                {{ $program->description ?? 'Program untuk jenjang ' . $program->name }}
                                            </p>
                                            @if(!empty($program->description))
                                                <button type="button"
                                                        class="read-more-btn text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium mt-1 flex items-center gap-1">
                                                    <span class="read-more-text">Baca selengkapnya</span>
                                                    <span class="hide-text hidden">Sembunyikan</span>
                                                    <i class="fa-solid fa-chevron-down text-xs read-more-icon transition-transform"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="mt-3 flex items-center gap-2">
                                            <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full">
                                                {{ $program->slug }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Checkmark --}}
                            <div class="absolute top-3 right-3 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                <i class="fa-solid fa-check text-xs"></i>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-2 text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-box-open text-gray-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">Belum ada program tersedia. Hubungi administrator.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Selected Info --}}
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 mb-6 border border-gray-100 dark:border-gray-600">
                    <div class="flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-circle-info text-gray-400 dark:text-gray-300"></i>
                        <span class="text-gray-600 dark:text-gray-200">Program terpilih: <strong id="selected-count" class="text-emerald-600 dark:text-emerald-300">0</strong></span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="{{ route('dashboard.onboarding.welcome') }}" 
                       class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm">
                        <i class="fa-solid fa-arrow-left mr-1"></i>
                        Kembali
                    </a>
                    
                    <div class="flex gap-3">
                        @if(count($selectedProgramIds) > 0)
                            <a href="{{ route('dashboard.onboarding.setup-guide') }}" 
                               class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-white dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium rounded-xl transition-colors">
                                Lewati
                            </a>
                        @endif
                        
                        <button type="submit" 
                                class="px-8 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors">
                            Simpan & Lanjutkan
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Update selected count
    function updateSelectedCount() {
        const checked = document.querySelectorAll('input[name="programs[]"]:checked');
        document.getElementById('selected-count').textContent = checked.length;
    }

    // Listen for changes
    document.querySelectorAll('input[name="programs[]"]').forEach(input => {
        input.addEventListener('change', updateSelectedCount);
    });

    // Initial count
    updateSelectedCount();

    // Read more/hide functionality
    document.querySelectorAll('.read-more-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const container = btn.closest('.description-container');
            const textEl = container.querySelector('.description-text');
            const readMoreText = btn.querySelector('.read-more-text');
            const hideText = btn.querySelector('.hide-text');
            const icon = btn.querySelector('.read-more-icon');

            const isExpanded = textEl.classList.contains('line-clamp-2');

            if (isExpanded) {
                // Expand
                textEl.classList.remove('line-clamp-2');
                readMoreText.classList.add('hidden');
                hideText.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                // Collapse
                textEl.classList.add('line-clamp-2');
                readMoreText.classList.remove('hidden');
                hideText.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        });
    });
</script>
@endsection
