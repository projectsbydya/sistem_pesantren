@php
$user = Auth::user();
$parent = $user->parent;
$children = $parent ? $parent->santri()->with('tenant')->get() : collect();
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Data Anak Saya</h3>
        <p class="text-sm text-gray-500 mt-1">Anda hanya dapat melihat data anak yang terdaftar</p>
    </div>

    @if($children->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($children as $santri)
                <div class="px-6 py-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full {{ $santri->gender === 'L' ? 'bg-blue-100' : 'bg-pink-100' }} flex items-center justify-center">
                            <span class="text-lg font-bold {{ $santri->gender === 'L' ? 'text-blue-600' : 'text-pink-600' }}">
                                {{ strtoupper(substr($santri->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $santri->name }}</p>
                                    <p class="text-sm text-gray-500">NIS: {{ $santri->nis }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $santri->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $santri->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Jenis Kelamin</p>
                                    <p class="font-medium">{{ $santri->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Tanggal Lahir</p>
                                    <p class="font-medium">{{ $santri->birth_date?->format('d M Y') ?? '-' }}</p>
                                </div>
                            </div>
                            @if($santri->school_level)
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-100 text-indigo-700">
                                        {{ $santri->school_level }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="px-6 py-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h4 class="text-gray-900 font-medium">Belum ada data anak</h4>
            <p class="text-sm text-gray-500 mt-1">Hubungi admin pesantren untuk mendaftarkan anak</p>
        </div>
    @endif
</div>
