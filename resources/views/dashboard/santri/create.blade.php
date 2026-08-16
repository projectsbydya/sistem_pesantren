@extends('layouts.tenant')

@section('title', 'Tambah Santri')

@section('breadcrumb')
    <span><a href="{{ tenant_route('dashboard.santri.index') }}" class="hover:text-emerald-600">Santri</a></span>
    <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
    <span>Tambah Baru</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

<!-- Header -->
<div class="mb-6">
    <a href="{{ tenant_route('dashboard.santri.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke daftar santri
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Tambah Santri Baru</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Isi data lengkap santri di bawah ini</p>
</div>

<x-card>
    @if($errors->any())
        <x-alert type="error" class="mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ tenant_route('dashboard.santri.store') }}" class="space-y-6">
        @csrf

        <!-- Section: Data Pribadi -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-user mr-2 text-emerald-600"></i>Data Pribadi
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        NIS <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nis" value="{{ old('nis') }}" required
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="Nomor Induk Santri">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Nomor unik untuk identifikasi santri</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="Nama lengkap santri">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Gender <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="gender" value="L" {{ old('gender') === 'L' ? 'checked' : '' }} class="peer sr-only">
                            <div class="text-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fa-solid fa-mars mr-1"></i> Laki-laki
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="gender" value="P" {{ old('gender') === 'P' ? 'checked' : '' }} class="peer sr-only">
                            <div class="text-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 peer-checked:bg-pink-500 peer-checked:text-white peer-checked:border-pink-500 transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fa-solid fa-venus mr-1"></i> Perempuan
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                </div>
            </div>
        </div>

        <!-- Section: Program -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-book mr-2 text-emerald-600"></i>Program
            </h3>

            @if($tenantPrograms->isEmpty())
                <div class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-sm text-amber-700 dark:text-amber-400">
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
                                   {{ in_array($program->slug, old('programs', [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 program-checkbox"
                                   data-program-slug="{{ $program->slug }}">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $program->name }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Dynamic Kelas Sections per Program --}}
                @foreach($tenantPrograms as $program)
                    @php
                        $programKelas = $program->kelas ?? collect([]);
                        $firstKelas = $programKelas->first();
                    @endphp
                    <div id="kelas_section_{{ $program->slug }}" class="{{ in_array($program->slug, old('programs', [])) ? '' : 'hidden' }} mb-4">
                        @if($programKelas->isEmpty())
                            <div class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-sm text-amber-700 dark:text-amber-400">
                                <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0"></i>
                                <span>
                                    Belum ada kelas untuk program {{ $program->name }}.
                                    @if($firstKelas)
                                        <a href="{{ tenant_route('dashboard.akademik.kelas.create', ['programSlug' => $program->slug]) }}" class="underline font-medium hover:text-amber-900 dark:hover:text-amber-200">
                                            Buat kelas terlebih dahulu
                                        </a>
                                    @endif
                                </span>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    Kelas {{ $program->name }} <span class="text-red-500 kelas-required-marker" data-program-slug="{{ $program->slug }}">*</span>
                                </label>
                                <select name="kelas_ids[{{ $program->slug }}]" id="kelas_id_{{ $program->slug }}"
                                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors program-kelas-select">
                                    <option value="">— Pilih Kelas {{ $program->name }} —</option>
                                    @foreach($programKelas as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_ids.' . $program->slug) == $k->id ? 'selected' : '' }}>
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

        <!-- Section: Data Akademik -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-graduation-cap mr-2 text-emerald-600"></i>Data Akademik
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenjang</label>
                    <select name="school_level"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">Pilih Jenjang</option>
                        <option value="Raudhah" {{ old('school_level') === 'Raudhah' ? 'selected' : '' }}>Raudhah</option>
                        <option value="Ibtidaiyah" {{ old('school_level') === 'Ibtidaiyah' ? 'selected' : '' }}>Ibtidaiyah</option>
                        <option value="Tsanawiyah" {{ old('school_level') === 'Tsanawiyah' ? 'selected' : '' }}>Tsanawiyah</option>
                        <option value="Aliyah" {{ old('school_level') === 'Aliyah' ? 'selected' : '' }}>Aliyah</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Sekolah Formal</label>
                    <input type="text" name="school_name" value="{{ old('school_name') }}"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="Contoh: SDN 01, SMPN 02, MTs Al-Hidayah">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                    <select name="status"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Masuk</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                </div>
                <div id="pesantren_section" class="sm:col-span-2">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-4">
                        <div>
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" name="is_mondok" id="is_mondok_create" value="1"
                                           {{ old('is_mondok') ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-10 h-5 bg-gray-200 dark:bg-gray-700 rounded-full peer-checked:bg-emerald-500 transition-colors"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mondok (tinggal di pesantren)</span>
                            </label>
                        </div>
                        <div id="kamar_section_create" class="{{ old('is_mondok') ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kamar</label>
                            <select name="kamar_id"
                                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                <option value="">— Pilih Kamar —</option>
                                @foreach($kamarList as $km)
                                    <option value="{{ $km->id }}" {{ old('kamar_id') == $km->id ? 'selected' : '' }}>{{ $km->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Data Orang Tua / Wali -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-users mr-2 text-emerald-600"></i>Data Orang Tua / Wali
            </h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Kosongkan jika tidak ada. Akun login orang tua akan dibuat otomatis.</p>

            {{-- Warning: Existing parent found by phone --}}
            @if(session('warning_parent_exists'))
                @php $wp = session('warning_parent_exists'); @endphp
                <div id="parent-exists-warning" class="mb-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 dark:text-amber-400 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">{{ $wp['message'] }}</p>
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                Nama: <strong>{{ $wp['parent_name'] }}</strong> | HP: {{ $wp['parent_phone'] }}
                            </p>
                            <label class="mt-3 flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="reuse_existing_parent" value="1" {{ old('reuse_existing_parent') ? 'checked' : '' }}
                                       class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-sm text-amber-700 dark:text-amber-300">Gunakan wali existing</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Orang Tua / Wali</label>
                    <input type="text" name="parent_name" value="{{ old('parent_name') }}"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="Nama lengkap orang tua / wali">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">NIK (opsional)</label>
                    <input type="text" name="parent_nik" value="{{ old('parent_nik') }}"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="16 digit NIK" maxlength="16">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Isi NIK untuk auto-link wali existing</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Hubungan</label>
                    <div class="flex gap-2">
                        @foreach(['father' => 'Ayah', 'mother' => 'Ibu', 'guardian' => 'Wali'] as $val => $label)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="parent_relationship" value="{{ $val }}"
                                       {{ old('parent_relationship', 'father') === $val ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="text-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-500 transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. Telepon</label>
                    <input type="tel" name="parent_phone" value="{{ old('parent_phone') }}"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="08xxxxxxxxxx">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Wajib diisi jika NIK kosong</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat Lengkap</label>
                    <textarea name="address" rows="3"
                              class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-none"
                              placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <x-btn href="{{ tenant_route('dashboard.santri.index') }}" variant="ghost">
                Batal
            </x-btn>
            <x-btn type="submit" variant="primary" icon="fa-save">
                Simpan Data
            </x-btn>
        </div>
    </form>
</x-card>

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
    const mondokToggle = document.getElementById('is_mondok_create');
    const kamarSection = document.getElementById('kamar_section_create');
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
