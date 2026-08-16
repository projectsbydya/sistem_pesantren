<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CredentialsExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithColumnWidths
{
    private array $rows;
    private string $title;

    public function __construct(array $credentials, string $loginUrl)
    {
        $this->title = 'Akun Login';
        $this->rows  = [];

        $no = 1;
        foreach ($credentials as $type => $data) {
            $this->rows[] = [
                $no++,
                $data['name']        ?? '-',
                $data['role']        ?? '-',
                $data['email']       ?? '-',
                $data['password']    ?? '-',
                'Ya — wajib ganti saat login pertama',
                $loginUrl,
                $data['santri_name'] ?? ($data['name'] ?? '-'),
            ];
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Role',
            'Email Login',
            'Password',
            'Ganti Password',
            'URL Login',
            'Untuk Santri',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'D' => 36,
            'E' => 22,
            'G' => 46,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF059669'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
