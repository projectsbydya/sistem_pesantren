@extends('layouts.tenant')

@section('title', 'Detail Orang Tua')
@section('page-title', 'Detail Orang Tua')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.parent.index') }}" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">Orang Tua</a>
    <i class="fa-solid fa-chevron-right text-[8px]"></i>
    <span class="text-gray-600 dark:text-gray-300">{{ $parent->name }}</span>
@endsection

@section('content')

{{-- Back link --}}
<div class="mb-6">
    <a href="{{ tenant_route('dashboard.parent.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        Kembali ke daftar
    </a>
</div>

{{-- Profile Header --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6 transition-colors">
    <div class="px-5 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-xl font-bold shrink-0">
            {{ strtoupper(substr($parent->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $parent->name }}</h1>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
                <span class="text-[12px] text-gray-500 dark:text-gray-400">{{ $parent->user?->email ?? $parent->email ?? 'Belum ada email' }}</span>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <x-badge variant="{{ $parent->is_active ? 'success' : 'default' }}" size="sm" dot>
                    {{ $parent->is_active ? 'Aktif' : 'Nonaktif' }}
                </x-badge>
                <x-badge variant="default" size="sm">
                    @switch($parent->relationship)
                        @case('father') Ayah @break
                        @case('mother') Ibu @break
                        @case('guardian') Wali @break
                        @default {{ $parent->relationship }}
                    @endswitch
                </x-badge>
            </div>
        </div>
        @if(Auth::user()->isAdmin())
            <x-btn href="{{ tenant_route('dashboard.parent.edit', ['id' => $parent->id]) }}" variant="outline" size="sm" icon="fa-pen">
                Edit
            </x-btn>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left Column: Info --}}
    <div class="lg:col-span-1 space-y-6">
        {{-- Contact Info --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">Informasi Kontak</h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Telepon</dt>
                    <dd class="text-[13px] text-gray-900 dark:text-gray-100">{{ $parent->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Email</dt>
                    <dd class="text-[13px] text-gray-900 dark:text-gray-100">{{ $parent->user?->email ?? $parent->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Alamat</dt>
                    <dd class="text-[13px] text-gray-900 dark:text-gray-100">{{ $parent->address ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Terdaftar</dt>
                    <dd class="text-[12px] text-gray-400 dark:text-gray-500">{{ $parent->created_at?->format('d M Y H:i') }}</dd>
                </div>
            </div>
        </div>

        {{-- Account Card --}}
        @if($parent->user && auth()->user()->can('resetPassword', $parent->user))
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors"
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
                    <span class="font-medium">Email:</span> {{ $parent->user->email }}
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
                            Reset Password — {{ $parent->user->name }}
                        </h4>
                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('dashboard.admin.users.reset-password', $parent->user->id) }}" class="p-5 space-y-4">
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
        @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">Status Akun</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-400 flex items-center justify-center">
                        <i class="fa-solid fa-minus"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-medium text-gray-700 dark:text-gray-300">Belum Punya Akun</p>
                        <p class="text-[11px] text-gray-400">Akun dapat dibuat dari menu admin</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column: Santri List --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">Daftar Santri</h3>
                <span class="text-[11px] text-gray-400">{{ $parent->santri->count() }} santri</span>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($parent->santri as $santri)
                    <div class="p-4 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center font-medium">
                            {{ strtoupper(substr($santri->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100 truncate">{{ $santri->name }}</p>
                            <div class="flex items-center gap-2 text-[11px] text-gray-400">
                                <span>NIS: {{ $santri->nis }}</span>
                                <span>·</span>
                                <span>{{ $santri->school_level ?? 'Tidak ada jenjang' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-badge variant="{{ $santri->status === 'active' ? 'success' : 'default' }}" size="sm">
                                {{ $santri->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                            <a href="{{ tenant_route('dashboard.santri.show', ['id' => $santri->id]) }}"
                               class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-md transition-colors"
                               title="Lihat detail santri">
                                <i class="fa-solid fa-eye text-[11px]"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <i class="fa-solid fa-user-graduate text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                        <p class="text-[13px] text-gray-500 dark:text-gray-400">Belum ada santri yang terhubung</p>
                        @if(Auth::user()->isAdmin())
                            <x-btn href="{{ tenant_route('dashboard.parent.edit', ['id' => $parent->id]) }}" variant="outline" size="sm" class="mt-3">
                                Hubungkan Santri
                            </x-btn>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
