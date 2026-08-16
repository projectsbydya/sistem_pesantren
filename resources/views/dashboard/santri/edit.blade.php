@extends('layouts.tenant')

@section('title', 'Edit Santri')
@section('page-title', 'Edit Santri')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.santri.index') }}" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">Santri</a>
    <i class="fa-solid fa-chevron-right text-[8px]"></i>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

{{-- Back link --}}
<div class="mb-6">
    <a href="{{ tenant_route('dashboard.santri.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        Kembali ke daftar
    </a>
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">Edit Santri</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $santri->name }} · {{ $santri->nis }}</p>
</div>

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="p-5">
        @if($errors->any())
            <x-alert type="error" class="mb-5">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ tenant_route('dashboard.santri.update', ['id' => $santri->id]) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Data Pribadi --}}
            <div>
                <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                    <i class="fa-solid fa-user mr-1.5 text-emerald-500"></i>Data Pribadi
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">NIS <span class="text-red-500">*</span></label>
                        <input type="text" name="nis" value="{{ old('nis', $santri->nis) }}"
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $santri->name) }}"
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Gender <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="gender" value="L" {{ old('gender', $santri->gender) === 'L' ? 'checked' : '' }} class="peer sr-only">
                                <div class="text-center px-4 py-2 text-[13px] border border-gray-200 dark:border-gray-700 rounded-lg peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 text-gray-700 dark:text-gray-300 transition-all hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <i class="fa-solid fa-mars mr-1"></i> Laki-laki
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="gender" value="P" {{ old('gender', $santri->gender) === 'P' ? 'checked' : '' }} class="peer sr-only">
                                <div class="text-center px-4 py-2 text-[13px] border border-gray-200 dark:border-gray-700 rounded-lg peer-checked:bg-pink-500 peer-checked:text-white peer-checked:border-pink-500 text-gray-700 dark:text-gray-300 transition-all hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <i class="fa-solid fa-venus mr-1"></i> Perempuan
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $santri->birth_date?->format('Y-m-d')) }}"
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                    </div>
                </div>
            </div>

            {{-- Program --}}
            <div>
                <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                    <i class="fa-solid fa-book mr-1.5 text-emerald-500"></i>Program
                </h3>
                @php
                    $santriProgramList = $santri->programs->pluck('program.slug')->toArray();
                    $oldPrograms = old('programs', $santriProgramList);
                @endphp

                @if($tenantPrograms->isEmpty())
                    <div class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-[13px] text-amber-700 dark:text-amber-400">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0"></i>
                        <span>
                            Belum ada program yang aktif untuk tenant ini.
                            Hubungi administrator untuk mengaktifkan program.
                        </span>
                    </div>
                @else
                    {{-- Dynamic Program Checkboxes --}}
                    <div class="flex gap-4 mb-4 flex-wrap">
                        @foreach($tenantPrograms as $program)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="programs[]" value="{{ $program->slug }}" id="program_{{ $program->slug }}"
                                       {{ in_array($program->slug, $oldPrograms) ? 'checked' : '' }}
                                       class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 program-checkbox"
                                       data-program-slug="{{ $program->slug }}">
                                <span class="text-[13px] text-gray-700 dark:text-gray-300">{{ $program->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- Dynamic Kelas Sections per Program --}}
                    @foreach($tenantPrograms as $program)
                        @php
                            $programKelas = $program->kelas ?? collect([]);
                            $santriProgramRecord = $santri->programs->firstWhere('program.slug', $program->slug);
                            $currentKelasId = old('kelas_ids.' . $program->slug, $santriProgramRecord?->kelas_id);
                        @endphp
                        <div id="kelas_section_{{ $program->slug }}" class="{{ in_array($program->slug, $oldPrograms) ? '' : 'hidden' }} mb-4">
                            @if($programKelas->isEmpty())
                                <div class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-[13px] text-amber-700 dark:text-amber-400">
                                    <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0"></i>
                                    <span>
                                        Belum ada kelas untuk program {{ $program->name }}.
                                        <a href="{{ tenant_route('dashboard.akademik.kelas.create', ['programSlug' => $program->slug]) }}" class="underline font-medium hover:text-amber-900 dark:hover:text-amber-200">
                                            Buat kelas terlebih dahulu
                                        </a>
                                    </span>
                                </div>
                            @else
                                <div>
                                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                        Kelas {{ $program->name }} <span class="text-red-500 kelas-required-marker" data-program-slug="{{ $program->slug }}">*</span>
                                    </label>
                                    <select name="kelas_ids[{{ $program->slug }}]" id="kelas_id_{{ $program->slug }}"
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors program-kelas-select">
                                        <option value="">— Pilih Kelas {{ $program->name }} —</option>
                                        @foreach($programKelas as $k)
                                            <option value="{{ $k->id }}" {{ $currentKelasId == $k->id ? 'selected' : '' }}>
                                                {{ $k->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Wajib diisi jika program {{ $program->name }} dipilih</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Data Akademik --}}
            <div>
                <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                    <i class="fa-solid fa-graduation-cap mr-1.5 text-emerald-500"></i>Data Akademik
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                        <select name="status"
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                            <option value="active" {{ old('status', $santri->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $santri->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenjang Sekolah</label>
                        <select name="school_level"
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                            <option value="">Pilih Jenjang</option>
                            <option value="Raudhah" {{ old('school_level', $santri->school_level) === 'Raudhah' ? 'selected' : '' }}>Raudhah</option>
                            <option value="Ibtidaiyah" {{ old('school_level', $santri->school_level) === 'Ibtidaiyah' ? 'selected' : '' }}>Ibtidaiyah</option>
                            <option value="Tsanawiyah" {{ old('school_level', $santri->school_level) === 'Tsanawiyah' ? 'selected' : '' }}>Tsanawiyah</option>
                            <option value="Aliyah" {{ old('school_level', $santri->school_level) === 'Aliyah' ? 'selected' : '' }}>Aliyah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Sekolah Formal</label>
                        <input type="text" name="school_name" value="{{ old('school_name', $santri->school_name) }}"
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors"
                               placeholder="Contoh: SDN 01, SMPN 02, MTs Al-Hidayah">
                    </div>
                    <div id="pesantren_section" class="sm:col-span-2">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-4">
                            <div>
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <div class="relative">
                                        <input type="checkbox" name="is_mondok" id="is_mondok_edit" value="1"
                                               {{ old('is_mondok', $santri->is_mondok) ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="w-10 h-5 bg-gray-200 dark:bg-gray-700 rounded-full peer-checked:bg-emerald-500 transition-colors"></div>
                                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                    </div>
                                    <span class="text-[13px] font-medium text-gray-700 dark:text-gray-300">Mondok (tinggal di pesantren)</span>
                                </label>
                            </div>
                            <div id="kamar_section_edit" class="{{ old('is_mondok', $santri->is_mondok) ? '' : 'hidden' }}">
                                <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kamar</label>
                                <select name="kamar_id"
                                        class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                                    <option value="">— Pilih Kamar —</option>
                                    @foreach($kamarList as $km)
                                        <option value="{{ $km->id }}" {{ old('kamar_id', $santri->kamar_id) == $km->id ? 'selected' : '' }}>{{ $km->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alamat --}}
            <div>
                <h3 class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                    <i class="fa-solid fa-location-dot mr-1.5 text-emerald-500"></i>Alamat
                </h3>
                <textarea name="address" rows="3"
                          class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors resize-none"
                          placeholder="Alamat lengkap">{{ old('address', $santri->address) }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                <x-btn href="{{ tenant_route('dashboard.santri.index') }}" variant="ghost">
                    Batal
                </x-btn>
                <x-btn type="submit" variant="primary" icon="fa-save">
                    Perbarui Data
                </x-btn>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
(function() {
    // Dynamic program checkbox handlers
    const programCheckboxes = document.querySelectorAll('.program-checkbox');

    function toggleProgramSection(programSlug) {
        const checkbox = document.getElementById('program_' + programSlug);
        const section = document.getElementById('kelas_section_' + programSlug);
        const select = document.getElementById('kelas_id_' + programSlug);
        const isChecked = checkbox?.checked;

        if (section) {
            section.classList.toggle('hidden', !isChecked);
        }
        // Make kelas required only when program is checked
        if (select) {
            select.required = !!isChecked;
        }
    }

    programCheckboxes.forEach(function(checkbox) {
        const programSlug = checkbox.dataset.programSlug;
        checkbox.addEventListener('change', function() {
            toggleProgramSection(programSlug);
        });
        // Set initial state
        toggleProgramSection(programSlug);
    });

    // Mondok toggle
    const mondokToggle = document.getElementById('is_mondok_edit');
    const kamarSection = document.getElementById('kamar_section_edit');
    if (mondokToggle && kamarSection) {
        mondokToggle.addEventListener('change', () => {
            kamarSection.classList.toggle('hidden', !mondokToggle.checked);
        });
    }
})();
</script>
@endpush

</div>{{-- max-w-3xl mx-auto --}}
@endsection
