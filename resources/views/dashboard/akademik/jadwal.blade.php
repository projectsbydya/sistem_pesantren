@extends('layouts.tenant')

@section('title', 'Jadwal Pelajaran')

@section('content')

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Jadwal Pelajaran</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Manajemen jadwal dan mata pelajaran — {{ \App\Services\TenantService::getTenant()?->name }}</p>
    </div>
    @if(Auth::user()->isAdmin())
        <x-btn href="#" variant="primary" icon="fa-plus">
            Tambah Jadwal
        </x-btn>
    @endif
</div>

<!-- Filter Cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 mb-6">
    @php
    $days = [
        ['id' => 'senin', 'label' => 'Senin', 'short' => 'Sen'],
        ['id' => 'selasa', 'label' => 'Selasa', 'short' => 'Sel'],
        ['id' => 'rabu', 'label' => 'Rabu', 'short' => 'Rab'],
        ['id' => 'kamis', 'label' => 'Kamis', 'short' => 'Kam'],
        ['id' => 'jumat', 'label' => 'Jumat', 'short' => 'Jum'],
        ['id' => 'sabtu', 'label' => 'Sabtu', 'short' => 'Sab'],
        ['id' => 'ahad', 'label' => 'Ahad', 'short' => 'Aha'],
    ];
    $activeDay = request('day', 'senin');
    @endphp
    
    @foreach($days as $day)
        <a href="{{ tenant_route('dashboard.akademik.jadwal', ['day' => $day['id']]) }}"
           class="text-center px-3 py-3 rounded-lg font-medium text-sm transition-all
                  {{ $activeDay === $day['id'] 
                     ? 'bg-emerald-600 text-white shadow-lg' 
                     : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700' }}">
            <span class="block sm:hidden">{{ $day['short'] }}</span>
            <span class="hidden sm:block">{{ $day['label'] }}</span>
        </a>
    @endforeach
</div>

<!-- Jadwal Table -->
<x-card title="Jadwal {{ ucfirst($activeDay) }}" subtitle="{{ \App\Services\TenantService::getTenant()?->name }}">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase w-24">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Mata Pelajaran</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Ustadz</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kelas</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Ruang</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @php
                $sampleSchedule = [
                    ['time' => '04:30 - 06:00', 'subject' => 'Tahfidz Quran', 'teacher' => 'Ust. Abdullah', 'class' => 'Semua', 'room' => 'Masjid'],
                    ['time' => '07:00 - 09:00', 'subject' => 'Fiqih', 'teacher' => 'Ust. Ahmad', 'class' => 'Tsanawiyah', 'room' => 'Kelas A'],
                    ['time' => '09:00 - 10:30', 'subject' => 'Bahasa Arab', 'teacher' => 'Ustz. Fatimah', 'class' => 'Aliyah', 'room' => 'Kelas B'],
                    ['time' => '10:30 - 12:00', 'subject' => 'Tafsir', 'teacher' => 'Ust. Muhammad', 'class' => 'Aliyah', 'room' => 'Kelas C'],
                    ['time' => '13:00 - 14:30', 'subject' => 'Nahwu', 'teacher' => 'Ust. Hasan', 'class' => 'Tsanawiyah', 'room' => 'Kelas A'],
                    ['time' => '15:00 - 16:30', 'subject' => 'Shorof', 'teacher' => 'Ust. Rofi', 'class' => 'Ibtidaiyah', 'room' => 'Kelas D'],
                    ['time' => '19:00 - 20:30', 'subject' => 'Malam Sholawat', 'teacher' => 'Ust. Yusuf', 'class' => 'Semua', 'room' => 'Aula'],
                ];
                @endphp
                
                @forelse($sampleSchedule as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 text-xs font-medium rounded">
                                <i class="fa-regular fa-clock"></i>
                                {{ $item['time'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <i class="fa-solid fa-book-open text-sm"></i>
                                </div>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $item['subject'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $item['teacher'] }}</td>
                        <td class="px-4 py-3">
                            <x-badge variant="info" size="sm">{{ $item['class'] }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $item['room'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <x-btn variant="ghost" size="xs" icon="fa-pen"></x-btn>
                                <x-btn variant="ghost" size="xs" icon="fa-trash" class="text-red-600 hover:text-red-700"></x-btn>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                            <i class="fa-solid fa-calendar-xmark text-4xl mb-3"></i>
                            <p>Tidak ada jadwal untuk hari ini</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

<!-- Ringkasan Mingguan -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <x-card title="Statistik Mingguan">
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Pelajaran</span>
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">42</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Ustadz Aktif</span>
                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">12</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <span class="text-sm text-gray-600 dark:text-gray-400">Jam Efektif / Minggu</span>
                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">28 Jam</span>
            </div>
        </div>
    </x-card>

    <x-card title="Ustadz Paling Sibuk" class="lg:col-span-2">
        <div class="space-y-3">
            @php
            $busyTeachers = [
                ['name' => 'Ust. Abdullah', 'hours' => 24, 'subjects' => ['Fiqih', 'Aqidah']],
                ['name' => 'Ustz. Fatimah', 'hours' => 20, 'subjects' => ['Bahasa Arab', 'Tajwid']],
                ['name' => 'Ust. Muhammad', 'hours' => 18, 'subjects' => ['Tafsir', 'Hadits']],
            ];
            @endphp
            
            @foreach($busyTeachers as $teacher)
                <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-white flex items-center justify-center font-bold">
                        {{ strtoupper(substr($teacher['name'], 5, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher['name'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ implode(', ', $teacher['subjects']) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $teacher['hours'] }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">jam/minggu</p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
</div>

@endsection
