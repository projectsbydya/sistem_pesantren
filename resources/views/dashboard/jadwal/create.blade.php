@extends('layouts.tenant')

@section('title', 'Tambah Jadwal ' . strtoupper($programSlug))
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.akademik.jadwal.index', ['programSlug' => $programSlug]) }}" class="hover:text-emerald-600">Jadwal {{ strtoupper($programSlug) }}</a>
    <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
    <span>Tambah</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">

<div class="mb-6">
    <a href="{{ tenant_route('dashboard.akademik.jadwal.index', ['programSlug' => $programSlug]) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke jadwal
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Tambah Jadwal {{ $program->name }}</h1>
</div>

<x-card>
    @if($errors->any())
        <x-alert type="error" class="mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    @include('dashboard.jadwal._form', [
        'action' => tenant_route('dashboard.akademik.jadwal.store', ['programSlug' => $programSlug]),
        'buttonLabel' => 'Simpan Jadwal',
        'schedule' => null,
    ])
</x-card>

</div>{{-- max-w-2xl mx-auto --}}
@endsection
