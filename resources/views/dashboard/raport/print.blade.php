<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport {{ $raport->santri?->name ?? '-' }} — {{ ucfirst($raport->semester) }} {{ $raport->tahun_ajaran }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 40px; color: #333; }
        h1 { margin: 0 0 8px; font-size: 24px; }
        h2 { margin: 24px 0 12px; font-size: 18px; border-bottom: 1px solid #ddd; padding-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; }
        .text-center { text-align: center; }
        .meta { margin-bottom: 24px; }
        .meta p { margin: 4px 0; }
        .signature { margin-top: 48px; display: flex; justify-content: space-between; }
        .signature-box { width: 200px; text-align: center; }
        .signature-line { border-top: 1px solid #333; margin-top: 64px; padding-top: 8px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 24px;">
        <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer;">Cetak</button>
        <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer; margin-left: 8px;">Tutup</button>
    </div>

    <h1>Raport Santri</h1>
    <p style="color: #666; margin-top: 4px;">{{ $program?->name ?? strtoupper($programSlug) }}</p>

    <div class="meta">
        <p><strong>Nama:</strong> {{ $raport->santri?->name ?? '-' }}</p>
        <p><strong>Kelas:</strong> {{ $raport->kelas?->name ?? '-' }}</p>
        <p><strong>Semester:</strong> {{ ucfirst($raport->semester) }} — {{ $raport->tahun_ajaran }}</p>
        <p><strong>Status:</strong> {{ ucfirst($raport->status) }}</p>
    </div>

    <h2>Nilai Akademik</h2>
    @php
        $componentCodes = collect();
        $componentLabels = [];
        foreach ($raport->nilaiRaport as $nilai) {
            foreach ($nilai->nilaiComponents as $component) {
                if (! $componentCodes->contains($component->assessment_code)) {
                    $componentCodes->push($component->assessment_code);
                    $componentLabels[$component->assessment_code] = $component->assessment_label;
                }
            }
        }
    @endphp
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Mata Pelajaran</th>
                @foreach($componentCodes as $code)
                    <th class="text-center">{{ $componentLabels[$code] ?? $code }}</th>
                @endforeach
                <th class="text-center">Akhir</th>
                <th class="text-center">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($raport->nilaiRaport as $index => $nilai)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $nilai->subject?->name ?? '-' }}</td>
                    @foreach($componentCodes as $code)
                        @php
                            $component = $nilai->nilaiComponents->firstWhere('assessment_code', $code);
                        @endphp
                        <td class="text-center">{{ $component?->score ?? '-' }}</td>
                    @endforeach
                    <td class="text-center">{{ $nilai->nilai_akhir }}</td>
                    <td class="text-center">{{ $nilai->predikat }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Absensi</h2>
    <table>
        <thead>
            <tr>
                <th class="text-center">Sakit</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Alpa</th>
                <th class="text-center">Total Hari Efektif</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ $raport->sakit }}</td>
                <td class="text-center">{{ $raport->izin }}</td>
                <td class="text-center">{{ $raport->alpa }}</td>
                <td class="text-center">{{ $raport->total_hari_efektif }}</td>
            </tr>
        </tbody>
    </table>

    @if($raport->catatan_umum)
        <h2>Catatan Umum</h2>
        <p>{{ $raport->catatan_umum }}</p>
    @endif

    <div class="signature">
        <div class="signature-box">
            <p>Orang Tua / Wali</p>
            <div class="signature-line"></div>
        </div>
        <div class="signature-box">
            <p>Kepala Pesantren</p>
            <div class="signature-line">{{ $raport->kepala_pesantren ?? '________________________' }}</div>
        </div>
    </div>
</body>
</html>
