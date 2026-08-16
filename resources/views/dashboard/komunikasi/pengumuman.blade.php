@extends('layouts.tenant')

@section('title', 'Pengumuman')

@section('content')

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Pengumuman</h1>
        <p class="text-sm text-gray-500 mt-1">Informasi dan pengumuman untuk wali santri</p>
    </div>
    @if(Auth::user()->isAdmin())
        <x-btn href="#" variant="primary" icon="fa-plus" onclick="document.getElementById('modal-pengumuman').classList.remove('hidden')">
            Buat Pengumuman
        </x-btn>
    @endif
</div>

<!-- Categories -->
<div class="flex flex-wrap gap-2 mb-6">
    <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium">Semua</button>
    <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium">Akademik</button>
    <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium">Keuangan</button>
    <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium">Kegiatan</button>
    <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium">Darurat</button>
</div>

<!-- Pengumuman List -->
<div class="space-y-4">
    @php
    $pengumumans = [
        [
            'id' => 1,
            'title' => 'Jadwal Ujian Tengah Semester (PTS)',
            'content' => 'Ujian tengah semester akan dilaksanakan pada tanggal 15-20 Maret 2024. Mohon untuk mempersiapkan santri dengan baik dan memastikan seluruh tagihan SPP sudah lunas sebelum ujian.',
            'category' => 'Akademik',
            'category_color' => 'blue',
            'author' => 'Kepala Madrasah',
            'date' => '2024-03-01',
            'is_important' => true,
            'views' => 128,
        ],
        [
            'id' => 2,
            'title' => 'Pemberitahuan Pembayaran SPP Bulan Maret',
            'content' => 'Dimohon kepada wali santri untuk melunasi pembayaran SPP bulan Maret paling lambat tanggal 10 Maret 2024. Pembayaran dapat dilakukan via transfer atau langsung ke bagian keuangan.',
            'category' => 'Keuangan',
            'category_color' => 'emerald',
            'author' => 'Bagian Keuangan',
            'date' => '2024-03-01',
            'is_important' => false,
            'views' => 96,
        ],
        [
            'id' => 3,
            'title' => 'Kegiatan Maulid Nabi Muhammad SAW',
            'content' => 'Dalam rangka memperingati Maulid Nabi Muhammad SAW, pesantren akan mengadakan acara tabligh akbar dan santunan anak yatim pada hari Sabtu, 9 September 2024. Semua santri wajib hadir.',
            'category' => 'Kegiatan',
            'category_color' => 'purple',
            'author' => 'OSIS',
            'date' => '2024-02-28',
            'is_important' => false,
            'views' => 245,
        ],
        [
            'id' => 4,
            'title' => 'Libur Nasional - Hari Raya Nyepi',
            'content' => 'Berdasarkan Surat Keputusan bersama, pesantren libur pada tanggal 11 Maret 2024 dalam rangka Hari Raya Nyepi. Santri diperbolehkan pulang atau tetap di asrama.',
            'category' => 'Kegiatan',
            'category_color' => 'purple',
            'author' => 'Kantor Pusat',
            'date' => '2024-02-25',
            'is_important' => false,
            'views' => 312,
        ],
        [
            'id' => 5,
            'title' => 'Penting: Perubahan Jadwal Sholat Subuh',
            'content' => 'Mulai tanggal 1 Maret 2024, jadwal sholat subuh diubah menjadi pukul 04:30 WIB. Semua santri wajib hadir tepat waktu. Ketidakhadiran tanpa keterangan akan dikenakan sanksi.',
            'category' => 'Darurat',
            'category_color' => 'red',
            'author' => 'Bagian Disiplin',
            'date' => '2024-02-20',
            'is_important' => true,
            'views' => 456,
        ],
    ];
    @endphp
    
    @foreach($pengumumans as $p)
        <div class="bg-white rounded-xl border {{ $p['is_important'] ? 'border-red-200 shadow-md' : 'border-gray-200' }} overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <x-badge variant="{{ $p['category_color'] }}" size="xs">{{ $p['category'] }}</x-badge>
                            @if($p['is_important'])
                                <x-badge variant="danger" size="xs" dot>Penting</x-badge>
                            @endif
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $p['title'] }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $p['content'] }}</p>
                    </div>
                    
                    @if($p['is_important'])
                        <div class="shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-bullhorn text-red-600"></i>
                        </div>
                    @endif
                </div>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-user text-xs"></i>
                            {{ $p['author'] }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-xs"></i>
                            {{ \Carbon\Carbon::parse($p['date'])->isoFormat('D MMM Y') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i class="fa-regular fa-eye text-xs"></i>
                            {{ $p['views'] }} views
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <x-btn variant="ghost" size="xs" icon="fa-share-nodes"></x-btn>
                        @if(Auth::user()->isAdmin())
                            <x-btn variant="ghost" size="xs" icon="fa-pen"></x-btn>
                            <x-btn variant="ghost" size="xs" icon="fa-trash" class="text-red-600"></x-btn>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Load More -->
<div class="mt-6 text-center">
    <x-btn variant="secondary" icon="fa-chevron-down">
        Muat lebih banyak
    </x-btn>
</div>

<!-- Modal: Buat Pengumuman (Hidden by default) -->
<div id="modal-pengumuman" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-pengumuman').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-title">Buat Pengumuman Baru</h3>
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                        <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option>Akademik</option>
                            <option>Keuangan</option>
                            <option>Kegiatan</option>
                            <option>Darurat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Isi Pengumuman</label>
                        <textarea rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="important" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="important" class="text-sm text-gray-700">Tandai sebagai penting</label>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <x-btn variant="primary" onclick="document.getElementById('modal-pengumuman').classList.add('hidden')">Publikasikan</x-btn>
                <x-btn variant="ghost" class="mt-3 sm:mt-0 sm:mr-3" onclick="document.getElementById('modal-pengumuman').classList.add('hidden')">Batal</x-btn>
            </div>
        </div>
    </div>
</div>

@endsection
