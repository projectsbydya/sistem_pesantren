@extends('layouts.tenant')

@section('title', 'Profil')

@section('content')
@php
$user   = Auth::user();
$tenant = App\Services\TenantService::getTenant();
$initials = collect(explode(' ', $user->name))
    ->map(fn ($part) => mb_substr($part, 0, 1))
    ->take(2)
    ->join('');
@endphp

<div class="max-w-5xl mx-auto space-y-6">

    {{-- Profile header --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-600 to-teal-600 p-6 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
            <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-3xl font-bold shrink-0">
                {{ strtoupper($initials) }}
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold truncate">{{ $user->name }}</h1>
                <p class="text-emerald-50 text-sm truncate">{{ $user->email }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-lg bg-white/20 px-3 py-1 text-sm font-medium backdrop-blur">
                        {{ $user->getRoleLabel() }}
                    </span>
                    @if ($tenant)
                        <span class="inline-flex items-center rounded-lg bg-white/10 px-3 py-1 text-sm backdrop-blur">
                            <i class="fa-solid fa-building mr-1.5"></i> {{ $tenant->name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Profile info & password --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    {{-- Delete account --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-red-200 dark:border-red-900/30 p-6 sm:p-8">
        @include('profile.partials.delete-user-form')
    </div>

</div>
@endsection
