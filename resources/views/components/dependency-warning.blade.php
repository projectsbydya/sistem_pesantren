@props([
    'warning' => 'Fitur belum tersedia',
    'message' => 'Silakan lengkapi setup terlebih dahulu.',
    'ctaText' => null,
    'ctaRoute' => null,
    'ctaParams' => [],
    'missing' => [],
])

<div class="min-h-[400px] flex items-center justify-center">
    <div class="text-center max-w-lg mx-auto p-8 bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-triangle-exclamation text-3xl text-amber-600"></i>
        </div>

        <h2 class="text-xl font-semibold text-slate-800 mb-3">
            {{ $warning }}
        </h2>

        <p class="text-slate-600 mb-6">
            {{ $message }}
        </p>

        @if(!empty($missing))
            <div class="flex flex-wrap gap-2 justify-center mb-6">
                @foreach($missing as $dep)
                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-sm">
                        <i class="fa-solid fa-circle-xmark text-red-500 mr-1"></i>
                        {{ ucfirst($dep) }}
                    </span>
                @endforeach
            </div>
        @endif

        @if($ctaRoute)
            <a href="{{ route($ctaRoute, $ctaParams) }}"
               class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-right mr-2"></i>
                {{ $ctaText ?? 'Lanjutkan' }}
            </a>
        @endif

        <div class="mt-6 text-sm text-slate-500">
            <a href="{{ route('dashboard.onboarding.setup-guide') }}" class="hover:text-indigo-600">
                <i class="fa-solid fa-circle-question mr-1"></i>
                Lihat panduan setup lengkap
            </a>
        </div>
    </div>
</div>
