<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Peran / Role <span class="text-red-500">*</span>
        </label>
        <select name="role" required
                class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            @foreach(\App\Models\Ustadz::getRoleOptions() as $value => $label)
                <option value="{{ $value }}" {{ old('role', isset($ustadz) ? $ustadz->role : null) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Status <span class="text-red-500">*</span>
        </label>
        <select name="status" required
                class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            @foreach(\App\Models\Ustadz::getStatusOptions() as $value => $label)
                <option value="{{ $value }}" {{ old('status', isset($ustadz) ? $ustadz->status : 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jam Mengajar / Minggu</label>
        <input type="number" name="jam_per_minggu" value="{{ old('jam_per_minggu', isset($ustadz) ? $ustadz->jam_per_minggu : '') }}" min="0" max="40"
               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
    </div>
    <div>
        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Performa (0-100)</label>
        <input type="number" name="performa" value="{{ old('performa', isset($ustadz) ? $ustadz->performa : '') }}" min="0" max="100"
               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
    </div>
    @php
        $selectedSubjectIds = old('subject_ids', isset($ustadz) ? $ustadz->subjects->pluck('id')->all() : []);
    @endphp
    <div class="sm:col-span-2">
        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Spesialisasi / Mata Pelajaran</label>
        <select name="subject_ids[]" multiple
                class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ in_array($subject->id, $selectedSubjectIds) ? 'selected' : '' }}>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>
        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1.5">Tahan Ctrl/Cmd untuk memilih lebih dari satu mata pelajaran.</p>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bio / Keterangan</label>
        <textarea name="bio" rows="3"
                  placeholder="Latar belakang, keahlian, pengalaman..."
                  class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors resize-none">{{ old('bio', isset($ustadz) ? $ustadz->bio : '') }}</textarea>
    </div>
</div>
