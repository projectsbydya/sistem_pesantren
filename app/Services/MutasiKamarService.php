<?php

namespace App\Services;

use App\Models\MutasiKamar;
use Illuminate\Support\Collection;

class MutasiKamarService
{
    /**
     * Get all mutations.
     */
    public function getAll(): Collection
    {
        return MutasiKamar::with([
            'santri',
            'kamarAsal',
            'kamarTujuan',
            'processedBy',
        ])
            ->orderBy('tanggal_mutasi', 'desc')
            ->get();
    }

    /**
     * Get mutations by date range.
     */
    public function getByDateRange(string $from, string $to): Collection
    {
        return MutasiKamar::byDateRange($from, $to)
            ->with([
                'santri',
                'kamarAsal',
                'kamarTujuan',
                'processedBy',
            ])
            ->orderBy('tanggal_mutasi', 'desc')
            ->get();
    }

    /**
     * Get mutations by kamar.
     */
    public function getByKamar(int $kamarId): Collection
    {
        return MutasiKamar::where(function ($q) use ($kamarId) {
            $q->where('kamar_asal_id', $kamarId)
                ->orWhere('kamar_tujuan_id', $kamarId);
        })
            ->with([
                'santri',
                'kamarAsal',
                'kamarTujuan',
                'processedBy',
            ])
            ->orderBy('tanggal_mutasi', 'desc')
            ->get();
    }

    /**
     * Get mutations by santri.
     */
    public function getBySantri(int $santriId): Collection
    {
        return MutasiKamar::where('santri_id', $santriId)
            ->with([
                'kamarAsal',
                'kamarTujuan',
                'processedBy',
            ])
            ->orderBy('tanggal_mutasi', 'desc')
            ->get();
    }

    /**
     * Create mutation record.
     */
    public function create(array $data): MutasiKamar
    {
        $data['tenant_id'] = tenant_id();
        $data['processed_by'] = auth()->id();
        $data['tanggal_mutasi'] = $data['tanggal_mutasi'] ?? now()->toDateString();

        return MutasiKamar::create($data);
    }

    /**
     * Get mutation statistics.
     */
    public function getStatistics(?string $from = null, ?string $to = null): array
    {
        $query = MutasiKamar::query();

        if ($from && $to) {
            $query->byDateRange($from, $to);
        }

        $total = $query->count();

        // Most active kamar asal
        $topAsal = MutasiKamar::selectRaw('kamar_asal_id, count(*) as total')
            ->when($from && $to, fn ($q) => $q->byDateRange($from, $to))
            ->groupBy('kamar_asal_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('kamarAsal')
            ->get();

        // Most active kamar tujuan
        $topTujuan = MutasiKamar::selectRaw('kamar_tujuan_id, count(*) as total')
            ->when($from && $to, fn ($q) => $q->byDateRange($from, $to))
            ->groupBy('kamar_tujuan_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('kamarTujuan')
            ->get();

        // By alasan
        $byAlasan = MutasiKamar::selectRaw('alasan, count(*) as total')
            ->when($from && $to, fn ($q) => $q->byDateRange($from, $to))
            ->groupBy('alasan')
            ->pluck('total', 'alasan');

        return [
            'total_mutasi' => $total,
            'top_asal' => $topAsal,
            'top_tujuan' => $topTujuan,
            'by_alasan' => $byAlasan,
        ];
    }
}
