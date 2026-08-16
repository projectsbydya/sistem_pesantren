@extends('layouts.tenant')

@section('title', 'Detail Santri')
@section('page-title', 'Detail Santri')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.santri.index') }}" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">Santri</a>
    <i class="fa-solid fa-chevron-right text-[8px]"></i>
    <span class="text-gray-600 dark:text-gray-300">{{ $santri->name }}</span>
@endsection

@section('content')

{{-- Profile Header --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6 transition-colors">
    <div class="px-5 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold shrink-0">
            {{ strtoupper(substr($santri->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $santri->name }}</h1>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
                <span class="font-mono text-[12px] text-gray-500 dark:text-gray-400">{{ $santri->nis }}</span>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                @if($santri->user?->email)
                    <span class="font-mono text-[12px] text-gray-400 dark:text-gray-500">{{ $santri->user->email }}</span>
                    <span class="text-gray-300 dark:text-gray-600">·</span>
                @endif
                <x-badge variant="{{ $santri->status === 'active' ? 'success' : 'default' }}" size="sm" dot>
                    {{ $santri->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                </x-badge>
                <x-badge variant="{{ $santri->gender === 'L' ? 'info' : 'pink' }}" size="sm">
                    {{ $santri->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                </x-badge>
            </div>
        </div>
        @if(Auth::user()->isAdmin())
            <x-btn href="{{ tenant_route('dashboard.santri.edit', ['id' => $santri->id]) }}" variant="outline" size="sm" icon="fa-pen">
                Edit
            </x-btn>
        @endif
    </div>
</div>

{{-- Detail Grid --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors mb-6">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">Informasi Santri</h3>
    </div>
    <div class="p-5">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">NIS</dt>
                <dd class="text-[13px] font-mono text-gray-900 dark:text-gray-100">{{ $santri->nis }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Nama Lengkap</dt>
                <dd class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $santri->name }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Gender</dt>
                <dd>
                    <x-badge variant="{{ $santri->gender === 'L' ? 'info' : 'pink' }}" size="sm">
                        {{ $santri->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </x-badge>
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Status</dt>
                <dd>
                    <x-badge variant="{{ $santri->status === 'active' ? 'success' : 'default' }}" size="sm" dot>
                        {{ $santri->status ?? '-' }}
                    </x-badge>
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Tanggal Lahir</dt>
                <dd class="text-[13px] text-gray-900 dark:text-gray-100">{{ $santri->birth_date?->format('d M Y') ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Jenjang</dt>
                <dd class="text-[13px] text-gray-900 dark:text-gray-100">{{ $santri->school_level ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Nama Sekolah Formal</dt>
                <dd class="text-[13px] text-gray-900 dark:text-gray-100">{{ $santri->school_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Mondok</dt>
                <dd class="text-[13px] text-gray-900 dark:text-gray-100">
                    @if($santri->is_mondok)
                        Ya{{ $santri->kamar ? ' — Kamar ' . $santri->kamar->name : '' }}
                    @else
                        Tidak
                    @endif
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Alamat</dt>
                <dd class="text-[13px] text-gray-900 dark:text-gray-100">{{ $santri->address ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Terdaftar</dt>
                <dd class="text-[12px] text-gray-400 dark:text-gray-500">{{ $santri->created_at?->format('d M Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>

{{-- Program --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors mb-6">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">
            <i class="fa-solid fa-book text-emerald-500 mr-2"></i>Program
        </h3>
    </div>
    @if($santri->programs->isNotEmpty())
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($santri->programs as $santriProgram)
                <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-semibold text-gray-900 dark:text-gray-100">{{ $santriProgram->program?->name ?? '-' }}</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400">
                            Kelas: {{ $santriProgram->kelas?->name ?? '-' }}
                        </p>
                    </div>
                    <x-badge variant="{{ $santriProgram->status === 'aktif' ? 'success' : 'default' }}" size="sm" dot>
                        {{ $santriProgram->status === 'aktif' ? 'Aktif' : ucfirst($santriProgram->status ?? '-') }}
                    </x-badge>
                </div>
            @endforeach
        </div>
    @else
        <div class="px-5 py-8 text-center">
            <i class="fa-solid fa-book-open text-2xl text-gray-300 dark:text-gray-600 mb-2"></i>
            <p class="text-[13px] text-gray-400 dark:text-gray-500">Belum terdaftar di program</p>
        </div>
    @endif
</div>

{{-- Orang Tua / Wali --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">
            <i class="fa-solid fa-users text-emerald-500 mr-2"></i>Orang Tua / Wali
        </h3>
    </div>
    @php
        $relationshipLabel = ['father' => 'Ayah', 'mother' => 'Ibu', 'guardian' => 'Wali'];
    @endphp
    @if($santri->parents->isNotEmpty())
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($santri->parents as $parent)
                <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr($parent->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-semibold text-gray-900 dark:text-gray-100">{{ $parent->name }}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <x-badge variant="info" size="sm">
                                {{ $relationshipLabel[$parent->pivot->relationship ?? $parent->relationship] ?? ucfirst($parent->relationship) }}
                            </x-badge>
                            @if($parent->pivot->is_primary)
                                <x-badge variant="success" size="sm">Utama</x-badge>
                            @endif
                            @if($parent->phone)
                                <span class="text-[12px] text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-phone text-[10px] mr-1"></i>{{ $parent->phone }}
                                </span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 mt-2 text-[12px] text-gray-500 dark:text-gray-400">
                            @if($parent->nik)
                                <span><span class="text-gray-400 dark:text-gray-500">NIK:</span> {{ $parent->nik }}</span>
                            @endif
                            @if($parent->user?->email ?? $parent->email)
                                <span><span class="text-gray-400 dark:text-gray-500">Email:</span> {{ $parent->user?->email ?? $parent->email }}</span>
                            @endif
                            @if($parent->address)
                                <span class="sm:col-span-2"><span class="text-gray-400 dark:text-gray-500">Alamat:</span> {{ $parent->address }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <x-badge variant="{{ $parent->is_active ? 'success' : 'default' }}" size="sm" dot>
                            {{ $parent->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="px-5 py-8 text-center">
            <i class="fa-solid fa-user-slash text-2xl text-gray-300 dark:text-gray-600 mb-2"></i>
            <p class="text-[13px] text-gray-400 dark:text-gray-500">Belum ada data orang tua / wali</p>
        </div>
    @endif
</div>

@if($santri->user && auth()->user()->can('resetPassword', $santri->user))
{{-- Account Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mt-6 transition-colors"
     x-data="{ open: false, showPw: false, showPwConfirm: false }">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">
            <i class="fa-solid fa-key text-emerald-500 mr-2"></i>Akun Login
        </h3>
        <button type="button" @click="open = true"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-medium text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
            <i class="fa-solid fa-rotate-right"></i>
            Reset Password
        </button>
    </div>
    <div class="p-5">
        <p class="text-[13px] text-gray-700 dark:text-gray-300">
            <span class="font-medium">Email:</span> {{ $santri->user->email }}
        </p>
        <p class="text-[12px] text-gray-400 dark:text-gray-500 mt-1">
            Reset password akan memaksa user ganti password saat login berikutnya.
        </p>
        @if(session('success'))
            <div class="mt-3 flex items-start gap-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg text-[12px] text-emerald-700 dark:text-emerald-400">
                <i class="fa-solid fa-circle-check mt-0.5 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         @keydown.escape.window="open = false">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xl w-full max-w-md mx-4"
             @click.outside="open = false">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h4 class="text-[14px] font-semibold text-gray-900 dark:text-gray-100">
                    Reset Password — {{ $santri->user->name }}
                </h4>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('dashboard.admin.users.reset-password', $santri->user->id) }}" class="p-5 space-y-4">
                @csrf
                <div class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-[12px] text-amber-700 dark:text-amber-400">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0"></i>
                    <span>User akan diwajibkan mengganti password saat login berikutnya.</span>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPw ? 'text' : 'password'" name="password" required autocomplete="new-password"
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pr-9 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                        <button type="button" @click="showPw = !showPw"
                                class="absolute inset-y-0 right-0 flex items-center px-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                            <i :class="showPw ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-[12px]"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Min. 8 karakter, huruf besar & kecil, angka, dan simbol.</p>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPwConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pr-9 text-[13px] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                        <button type="button" @click="showPwConfirm = !showPwConfirm"
                                class="absolute inset-y-0 right-0 flex items-center px-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                            <i :class="showPwConfirm ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-[12px]"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-[13px] font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors">
                        <i class="fa-solid fa-rotate-right mr-1.5"></i>Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
