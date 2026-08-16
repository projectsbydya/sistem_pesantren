<?php

namespace App\Exports;

use App\Models\Tabungan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TabunganExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private ?string $jenis = null,
        private ?int $santriId = null,
    ) {}

    public function query()
    {
        $query = Tabungan::with(['santri.parents'])->orderBy('tanggal', 'desc');

        if ($this->jenis) {
            $query->where('jenis', $this->jenis);
        }
        if ($this->santriId) {
            $query->where('santri_id', $this->santriId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Santri',
            'Nama Orang Tua',
            'No HP Orang Tua',
            'Tanggal',
            'Jenis',
            'Jumlah (Rp)',
            'Keterangan',
        ];
    }

    private int $row = 0;

    public function map($t): array
    {
        $this->row++;
        $parent = $t->santri?->parents?->first();

        return [
            $this->row,
            $t->santri?->name ?? '-',
            $parent?->name ?? '-',
            $parent?->phone ?? '-',
            $t->tanggal?->format('d/m/Y'),
            Tabungan::JENIS_LABELS[$t->jenis] ?? $t->jenis,
            (float) $t->jumlah,
            $t->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Rekap Tabungan';
    }
}
