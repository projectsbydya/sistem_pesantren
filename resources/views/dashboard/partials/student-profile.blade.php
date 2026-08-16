@php
$user = Auth::user();
$santri = $user->santri;
@endphp

@if($santri)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="text-center">
                    <div class="w-24 h-24 rounded-full {{ $santri->gender === 'L' ? 'bg-blue-100' : 'bg-pink-100' }} flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold {{ $santri->gender === 'L' ? 'text-blue-600' : 'text-pink-600' }}">
                            {{ strtoupper(substr($santri->name, 0, 1)) }}
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $santri->name }}</h3>
                    <p class="text-sm text-gray-500">NIS: {{ $santri->nis }}</p>
                    <div class="mt-4">
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $santri->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $santri->status === 'active' ? 'Santri Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pesantren</span>
                        <span class="font-medium text-gray-900">{{ $santri->tenant?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Jenjang</span>
                        <span class="font-medium text-gray-900">{{ $santri->school_level ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Jenis Kelamin</span>
                        <span class="font-medium text-gray-900">{{ $santri->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pribadi</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Lahir</p>
                        <p class="font-medium text-gray-900">{{ $santri->birth_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Alamat</p>
                        <p class="font-medium text-gray-900">{{ $santri->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Parent Info -->
            @if($santri->parents->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Orang Tua</h4>
                    <div class="space-y-4">
                        @foreach($santri->parents as $parent)
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $parent->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $parent->pivot->relationship === 'father' ? 'Ayah' : ($parent->pivot->relationship === 'mother' ? 'Ibu' : 'Wali') }}
                                        @if($parent->pivot->is_primary)
                                            <span class="text-emerald-600">• Utama</span>
                                        @endif
                                    </p>
                                    @if($parent->phone)
                                        <p class="text-sm text-gray-500 mt-1">{{ $parent->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h4 class="text-gray-900 font-medium">Data santri tidak ditemukan</h4>
        <p class="text-sm text-gray-500 mt-1">Hubungi admin untuk informasi lebih lanjut</p>
    </div>
@endif
