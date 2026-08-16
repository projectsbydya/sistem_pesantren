@extends('layouts.tenant')

@section('title', 'Hafalan Quran — ' . $santri->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Hafalan Quran</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $santri->name }} — {{ $santri->kelas?->name ?? 'Tanpa Kelas' }}</p>
    </div>

    @if($records->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <h3 class="text-lg font-semibold text-gray-700">Belum ada data hafalan quran</h3>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Surah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Ayat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Juz</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Nilai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($records as $rec)
                        @php $color = \App\Models\HafalanQuran::STATUS_COLORS[$rec->status] ?? 'gray'; @endphp
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $rec->tanggal->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $rec->surah }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $rec->ayat_dari }}{{ $rec->ayat_sampai ? ' - ' . $rec->ayat_sampai : '' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $rec->juz ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm font-semibold">{{ $rec->nilai !== null ? number_format($rec->nilai, 1) : '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 text-{{ $color }}-700">
                                    {{ \App\Models\HafalanQuran::STATUS_LABELS[$rec->status] }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $rec->catatan ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
