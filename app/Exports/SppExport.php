<?php

namespace App\Exports;

use App\Models\Bill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SppExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private ?string $status = null,
        private ?string $type = null,
        private ?int $santriId = null,
    ) {}

    public function query()
    {
        $query = Bill::with(['santri.parents'])->orderBy('due_date', 'desc');

        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->type) {
            $query->where('type', $this->type);
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
            'Jenis Tagihan',
            'Jumlah (Rp)',
            'Jatuh Tempo',
            'Status',
            'Keterangan',
        ];
    }

    private int $row = 0;

    public function map($bill): array
    {
        $this->row++;
        $parent = $bill->santri?->parents?->first();

        return [
            $this->row,
            $bill->santri?->name ?? '-',
            $parent?->name ?? '-',
            $parent?->phone ?? '-',
            Bill::TYPE_LABELS[$bill->type] ?? $bill->type,
            (float) $bill->amount,
            $bill->due_date?->format('d/m/Y'),
            Bill::STATUS_LABELS[$bill->status] ?? $bill->status,
            $bill->description ?? '-',
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
        return 'Rekap SPP';
    }
}
