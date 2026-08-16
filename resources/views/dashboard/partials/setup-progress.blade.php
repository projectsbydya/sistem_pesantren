@php
$showSetupWidget = $showSetupWidget ?? true;
@endphp

@if ($showSetupWidget)
@php
$actualProgress = [];
try {
    $actualProgress = \App\Services\TenantSetupService::getActualProgress();
} catch (\Exception $e) {
    // Silently fail — setup not initialised yet
}

$apPercentage  = $actualProgress['percentage']  ?? 0;
$apIsComplete  = $actualProgress['is_complete']  ?? false;
$apNextAction  = $actualProgress['next_action']  ?? null;

$checklistItems = [
    ['label' => 'Program',            'done' => $actualProgress['hasProgram']     ?? false, 'icon' => 'fa-layer-group',     'required' => true],
    ['label' => 'Kelas',              'done' => $actualProgress['hasKelas']       ?? false, 'icon' => 'fa-chalkboard',      'required' => true],
    ['label' => 'Mata Pelajaran',     'done' => $actualProgress['hasSubject']     ?? false, 'icon' => 'fa-book',            'required' => true],
    ['label' => 'Ustadz',             'done' => $actualProgress['hasUstadz']      ?? false, 'icon' => 'fa-chalkboard-user', 'required' => true],
    ['label' => 'Penugasan Mengajar', 'done' => $actualProgress['hasUstadzKelas'] ?? false, 'icon' => 'fa-user-tie',        'required' => true],
    ['label' => 'Jadwal',             'done' => $actualProgress['hasSchedule']    ?? false, 'icon' => 'fa-calendar-days',   'required' => true],
    ['label' => 'Santri',             'done' => $actualProgress['hasSantri']      ?? false, 'icon' => 'fa-user-graduate',   'required' => false],
];
@endphp

@if (!empty($actualProgress))
<div class="mb-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    {{-- Card header --}}
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                <i class="fa-solid fa-tasks text-emerald-600 dark:text-emerald-400 text-sm"></i>
            </div>
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 text-sm">Setup Akademik</h3>
        </div>
        <span class="text-xs font-bold {{ $apIsComplete ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/20' : 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/20' }} px-2.5 py-1 rounded-full">
            {{ $apPercentage }}%
        </span>
    </div>

    {{-- Progress bar --}}
    <div class="px-5 pt-3">
        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
            <div class="h-1.5 rounded-full transition-all duration-500 {{ $apIsComplete ? 'bg-emerald-500' : 'bg-amber-400' }}"
                 style="width: {{ $apPercentage }}%"></div>
        </div>
    </div>

    {{-- Checklist --}}
    <div class="px-5 py-4 space-y-2">
        @foreach ($checklistItems as $item)
        <div class="flex items-center gap-3">
            @if ($item['done'])
                <div class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400" style="font-size:0.6rem"></i>
                </div>
            @else
                <div class="w-5 h-5 rounded-full border-2 flex-shrink-0
                    {{ $item['required'] ? 'border-amber-400 dark:border-amber-500' : 'border-gray-300 dark:border-gray-600' }}"></div>
            @endif
            <span class="text-sm flex-1
                {{ $item['done'] ? 'text-gray-400 dark:text-gray-500 line-through' : ($item['required'] ? 'text-gray-800 dark:text-gray-200 font-medium' : 'text-gray-500 dark:text-gray-400') }}">
                {{ $item['label'] }}
                @if (!$item['required'])
                    <span class="text-xs text-gray-400 dark:text-gray-500 font-normal not-italic no-underline">(opsional)</span>
                @endif
            </span>
            @if ($item['done'])
                <i class="fa-solid {{ $item['icon'] }} text-emerald-300 dark:text-emerald-500 text-xs"></i>
            @elseif ($item['required'])
                <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Wajib</span>
            @endif
        </div>
        @endforeach
    </div>

    {{-- CTA --}}
    <div class="px-5 pb-5">
        @if ($apNextAction)
            <a href="{{ route($apNextAction['route'], $apNextAction['params'] ?? []) }}"
               class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                      bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition">
                <i class="fa-solid {{ $apNextAction['icon'] ?? 'fa-arrow-right' }}"></i>
                Lanjutkan Setup
            </a>
        @else
            <a href="{{ route('dashboard.onboarding.wizard', ['step' => 'ringkasan']) }}"
               class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                      bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl transition">
                <i class="fa-solid fa-check-circle text-emerald-500 dark:text-emerald-400"></i>
                Lihat Ringkasan Setup
            </a>
        @endif
    </div>
</div>
@endif
@endif
