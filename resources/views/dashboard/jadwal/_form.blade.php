@php
$selectedUstadzKelas = old('ustadz_kelas_id', $schedule?->ustadz_kelas_id);
$ensureUrl = tenant_route('dashboard.akademik.penugasan.ensure', ['programSlug' => $programSlug]);
@endphp

<form method="POST"
      action="{{ $action }}"
      x-ref="form"
      x-data="scheduleForm({
          assignments: @js($ustadzKelas->map(fn ($uk) => [
              'id' => $uk->id,
              'ustadz_id' => $uk->ustadz_id,
              'kelas_id' => $uk->kelas_id,
              'subject_id' => $uk->subject_id,
              'ustadz_name' => $uk->ustadz?->user?->name ?? '?',
          ])),
          ustadzList: @js($ustadzList->map(fn ($u) => ['id' => $u->id, 'name' => $u->user?->name ?? '?'])),
          subjects: @js($subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])),
          kelas: @js($kelas->map(fn ($k) => ['id' => $k->id, 'name' => $k->name])),
          kelasSubjectsMap: @js($kelasSubjectsMap),
          ensureUrl: @js($ensureUrl),
          canCreateAssignment: @js($canCreateAssignment),
          selectedUstadzKelas: @js($selectedUstadzKelas),
      })"
      x-init="init"
      class="space-y-5">
    @csrf
    @if(!empty($method)) @method($method) @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if($ustadzKelas->isEmpty() && !$canCreateAssignment)
            <div class="sm:col-span-2">
                <div class="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 px-3 py-2.5 text-[12px] text-amber-700 dark:text-amber-400">
                    Belum ada penugasan ustadz ke kelas untuk program {{ $program->name }}.
                    Tambahkan terlebih dahulu melalui menu <strong>Jadwal Mengajar → Penugasan Mengajar</strong>.
                </div>
            </div>
        @else
            {{-- Kelas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Kelas <span class="text-red-500">*</span>
                </label>
                <select name="kelas_id" x-model="selectedKelas" @change="onKelasChange" required
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    <option value="">Pilih Kelas</option>
                    <template x-for="k in kelas" :key="k.id">
                        <option :value="k.id" x-text="k.name"></option>
                    </template>
                </select>
            </div>

            {{-- Mata Pelajaran --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Mata Pelajaran <span class="text-red-500">*</span>
                </label>
                <select name="subject_id" x-model="selectedSubject" @change="onSubjectChange" required
                        :disabled="!selectedKelas"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                    <option value="">Pilih Mata Pelajaran</option>
                    <template x-for="s in filteredSubjects" :key="s.id">
                        <option :value="s.id" x-text="s.name"></option>
                    </template>
                </select>
                <template x-if="selectedKelas && filteredSubjects.length === 0">
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Tidak ada mata pelajaran untuk kelas ini.</p>
                </template>
            </div>

            {{-- Ustadz --}}
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Ustadz <span class="text-red-500">*</span>
                </label>
                <select name="ustadz_id" x-model="selectedUstadz" @change="onUstadzChange" required
                        :disabled="!selectedSubject"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                    <option value="">Pilih Ustadz</option>
                    <template x-for="u in ustadzList" :key="u.id">
                        <option :value="u.id" x-text="u.name"></option>
                    </template>
                </select>
                <template x-if="selectedSubject && !hasAssignmentForSelection && !canCreateAssignment">
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Tidak ada ustadz yang bertugas untuk kelas &amp; mata pelajaran ini.</p>
                </template>
                <template x-if="selectedSubject && !hasAssignmentForSelection && canCreateAssignment">
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Ustadz belum ditugaskan untuk kelas &amp; mata pelajaran ini. Penugasan akan dibuat otomatis saat menyimpan.</p>
                </template>
                @error('ustadz_kelas_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                <p x-show="assignmentError" x-text="assignmentError" class="text-xs text-red-500 mt-1"></p>
            </div>

            {{-- Resolved teaching assignment id sent to the backend --}}
            <input type="hidden" name="ustadz_kelas_id" :value="resolvedUstadzKelasId">
        @endif

        {{-- Hari --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Hari <span class="text-red-500">*</span>
            </label>
            <select name="hari" required
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                <option value="">Pilih Hari</option>
                @foreach(\App\Models\Schedule::HARI as $hari)
                    <option value="{{ $hari }}" {{ old('hari', $schedule?->hari) === $hari ? 'selected' : '' }}>{{ $hari }}</option>
                @endforeach
            </select>
            @error('hari')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Jam Mulai --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Jam Mulai <span class="text-red-500">*</span>
            </label>
            <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $schedule ? substr($schedule->jam_mulai, 0, 5) : '') }}" required
                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            @error('jam_mulai')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Jam Selesai --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Jam Selesai <span class="text-red-500">*</span>
            </label>
            <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $schedule ? substr($schedule->jam_selesai, 0, 5) : '') }}" required
                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            @error('jam_selesai')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <x-btn href="{{ tenant_route('dashboard.akademik.jadwal.index', ['programSlug' => $programSlug]) }}" variant="ghost">Batal</x-btn>
        <x-btn type="button"
               variant="primary"
               icon="fa-save"
               @click="submitOrEnsure"
               :disabled="$ustadzKelas->isEmpty() && !$canCreateAssignment">
            <span x-text="resolvedUstadzKelasId ? '{{ $buttonLabel }}' : (canCreateAssignment ? 'Buat Penugasan & Lanjutkan' : 'Pilih Ustadz')"></span>
        </x-btn>
    </div>
</form>

<script>
    function scheduleForm(config) {
        return {
            assignments: config.assignments,
            ustadzList: config.ustadzList,
            subjects: config.subjects,
            kelas: config.kelas,
            kelasSubjectsMap: config.kelasSubjectsMap ?? {},
            ensureUrl: config.ensureUrl,
            canCreateAssignment: config.canCreateAssignment,
            selectedKelas: null,
            selectedSubject: null,
            selectedUstadz: null,
            resolvedUstadzKelasId: config.selectedUstadzKelas,
            assignmentError: null,
            isProcessing: false,

            get filteredSubjects() {
                if (!this.selectedKelas) return [];

                const mappedIds = this.kelasSubjectsMap[this.selectedKelas];
                if (mappedIds && mappedIds.length > 0) {
                    return this.subjects.filter(s => mappedIds.includes(s.id));
                }

                const subjectIds = [...new Set(
                    this.assignments
                        .filter(a => a.kelas_id == this.selectedKelas)
                        .map(a => a.subject_id)
                )];

                if (subjectIds.length > 0) {
                    return this.subjects.filter(s => subjectIds.includes(s.id));
                }

                return this.canCreateAssignment ? this.subjects : [];
            },

            get hasAssignmentForSelection() {
                if (!this.selectedKelas || !this.selectedSubject || !this.selectedUstadz) return false;
                return this.assignments.some(a =>
                    a.kelas_id == this.selectedKelas &&
                    a.subject_id == this.selectedSubject &&
                    a.ustadz_id == this.selectedUstadz
                );
            },

            init() {
                if (this.resolvedUstadzKelasId) {
                    const assignment = this.assignments.find(a => a.id == this.resolvedUstadzKelasId);
                    if (assignment) {
                        this.selectedKelas = assignment.kelas_id;
                        this.selectedSubject = assignment.subject_id;
                        this.selectedUstadz = assignment.ustadz_id;
                    }
                }
                this.resolve();
            },

            resolve() {
                if (!this.selectedKelas || !this.selectedSubject || !this.selectedUstadz) {
                    this.resolvedUstadzKelasId = null;
                    return;
                }

                const assignment = this.assignments.find(a =>
                    a.kelas_id == this.selectedKelas &&
                    a.subject_id == this.selectedSubject &&
                    a.ustadz_id == this.selectedUstadz
                );

                this.resolvedUstadzKelasId = assignment ? assignment.id : null;
                this.assignmentError = null;
            },

            onKelasChange() {
                this.selectedSubject = null;
                this.selectedUstadz = null;
                this.resolve();
            },

            onSubjectChange() {
                this.selectedUstadz = null;
                this.resolve();
            },

            onUstadzChange() {
                this.resolve();
            },

            async submitOrEnsure() {
                if (this.resolvedUstadzKelasId) {
                    this.$refs.form.submit();
                    return;
                }

                if (!this.canCreateAssignment || !this.selectedKelas || !this.selectedSubject || !this.selectedUstadz) {
                    return;
                }

                this.isProcessing = true;
                this.assignmentError = null;

                try {
                    const csrfToken = this.$refs.form.querySelector('input[name=_token]').value;
                    const response = await fetch(this.ensureUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            ustadz_id: this.selectedUstadz,
                            kelas_id: this.selectedKelas,
                            subject_id: this.selectedSubject,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal membuat penugasan.');
                    }

                    const selectedUstadzName = this.ustadzList.find(u => u.id == this.selectedUstadz)?.name || '?';

                    this.assignments.push({
                        id: data.ustadz_kelas_id,
                        ustadz_id: this.selectedUstadz,
                        kelas_id: this.selectedKelas,
                        subject_id: this.selectedSubject,
                        ustadz_name: selectedUstadzName,
                    });

                    this.resolvedUstadzKelasId = data.ustadz_kelas_id;
                    this.$refs.form.submit();
                } catch (e) {
                    this.assignmentError = e.message;
                } finally {
                    this.isProcessing = false;
                }
            },
        };
    }
</script>
