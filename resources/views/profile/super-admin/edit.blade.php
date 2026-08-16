@extends('layouts.app')

@section('title', 'Profile Super Admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 py-8 px-4 sm:px-6 lg:px-8">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Profile Super Admin</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola informasi akun super admin Anda</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-sm">
        @include('profile.super-admin.partials.update-profile-information-form')
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-sm">
        @include('profile.super-admin.partials.update-password-form')
    </div>

</div>
@endsection
