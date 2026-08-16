@extends('layouts.tenant')

@section('title', 'Monitoring Sholat - ' . $program->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Monitoring Sholat</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $program->name }} &mdash; Pemantauan sholat santri</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Tambah Record</h3>
        <form method="POST" action="{{ tenant_route('dashboard.diniyah.monitoring-sholat.store', ['programSlug' => $programSlug]) }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
                <select id="kelas_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="">Pilih Kelas</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" data-santri="{{ base64_encode(json_encode($kelas->santri->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values())) }}">
                            {{ $kelas->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Santri</label>
                <select name="santri_id" id="santri_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="">Pilih Kelas dahulu</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ today()->toDateString() }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Waktu Sholat</label>
                <select name="aspect" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @foreach(\App\Models\DiniyahMonitoring::SHOLAT_TIMES as $wkt)
                        <option value="{{ $wkt }}">{{ \App\Models\DiniyahMonitoring::SHOLAT_TIME_LABELS[$wkt] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @foreach(\App\Models\DiniyahMonitoring::SHOLAT_STATUSES as $st)
                        <option value="{{ $st }}">{{ \App\Models\DiniyahMonitoring::SHOLAT_STATUS_LABELS[$st] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Berjamaah</label>
                <select name="flag" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="0">Tidak</option>
                    <option value="1">Ya</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
                    <i class="fa-solid fa-plus mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>

    {{-- Filter by Kelas — pilih kelas dahulu sebelum menampilkan daftar santri --}}
    <form method="GET" action="{{ tenant_route('dashboard.diniyah.monitoring-sholat.index', ['programSlug' => $programSlug]) }}"
          class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-6 flex flex-col sm:flex-row items-end gap-3">
        <div class="flex-1 sm:max-w-xs">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
            <select name="kelas_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" {{ (int) $kelasId === $kelas->id ? 'selected' : '' }}>{{ $kelas->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-semibold hover:bg-gray-800">
            <i class="fa-solid fa-filter mr-1"></i> Filter
        </button>
        @if($kelasId)
            <a href="{{ tenant_route('dashboard.diniyah.monitoring-sholat.index', ['programSlug' => $programSlug]) }}"
               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                Reset
            </a>
        @endif
    </form>

    @if($records->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-mosque text-gray-400 dark:text-gray-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada data monitoring sholat</h3>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Berjamaah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($records as $rec)
                        @php $color = \App\Models\DiniyahMonitoring::STATUS_COLORS[$rec->status] ?? 'gray'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $rec->date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rec->santri?->name }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">{{ \App\Models\DiniyahMonitoring::SHOLAT_TIME_LABELS[$rec->aspect] ?? $rec->aspect }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 text-{{ $color }}-700 dark:text-{{ $color }}-400">
                                    {{ \App\Models\DiniyahMonitoring::SHOLAT_STATUS_LABELS[$rec->status] ?? $rec->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm">
                                @if($rec->flag)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Ya</span>
                                @else
                                    <span class="text-gray-400">Tidak</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ tenant_route('dashboard.diniyah.monitoring-sholat.destroy', ['programSlug' => $programSlug, 'id' => $rec->id]) }}" onsubmit="return confirm('Hapus record ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
    (function () {
        const kelasSelect = document.getElementById('kelas_id');
        const santriSelect = document.getElementById('santri_id');

        kelasSelect.addEventListener('change', function () {
            santriSelect.innerHTML = '<option value="">Pilih Santri</option>';
            const selected = this.options[this.selectedIndex];
            const santriList = JSON.parse(atob(selected.dataset.santri || '') || '[]');
            santriList.forEach(function (s) {
                const option = document.createElement('option');
                option.value = s.id;
                option.textContent = s.name;
                santriSelect.appendChild(option);
            });
        });
    })();
</script>
@endsection
