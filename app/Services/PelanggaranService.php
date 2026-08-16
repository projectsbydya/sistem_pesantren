<?php

namespace App\Services;

use App\Models\Pelanggaran;
use Illuminate\Support\Collection;

class PelanggaranService
{
    /**
     * Get all violations.
     */
    public function getAll(): Collection
    {
        return Pelanggaran::with([
            'santri',
            'pelapor',
            'sanksi',
        ])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    /**
     * Get violations by status.
     */
    public function getByStatus(string $status): Collection
    {
        return Pelanggaran::byStatus($status)
            ->with([
                'santri',
                'pelapor',
                'sanksi',
            ])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    /**
     * Get violations by jenis.
     */
    public function getByJenis(string $jenis): Collection
    {
        return Pelanggaran::byJenis($jenis)
            ->with([
                'santri',
                'pelapor',
                'sanksi',
            ])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    /**
     * Get violations by santri.
     */
    public function getBySantri(int $santriId): Collection
    {
        return Pelanggaran::where('santri_id', $santriId)
            ->with([
                'pelapor',
                'sanksi',
            ])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    /**
     * Get violations by date range.
     */
    public function getByDateRange(string $from, string $to): Collection
    {
        return Pelanggaran::byDateRange($from, $to)
            ->with([
                'santri',
                'pelapor',
                'sanksi',
            ])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    /**
     * Create violation.
     */
    public function create(array $data): Pelanggaran
    {
        $data['tenant_id'] = tenant_id();
        $data['pelapor_id'] = auth()->id();
        $data['status'] = $data['status'] ?? Pelanggaran::STATUS_PENDING;
        $data['tanggal'] = $data['tanggal'] ?? now()->toDateString();

        return Pelanggaran::create($data);
    }

    /**
     * Update violation.
     */
    public function update(Pelanggaran $pelanggaran, array $data): Pelanggaran
    {
        $pelanggaran->update($data);
        return $pelanggaran->fresh();
    }

    /**
     * Process violation (change status to diproses).
     */
    public function process(Pelanggaran $pelanggaran, ?string $tindakLanjut = null): Pelanggaran
    {
        $pelanggaran->update([
            'status' => Pelanggaran::STATUS_DIPROSES,
            'tindak_lanjut' => $tindakLanjut,
        ]);

        return $pelanggaran->fresh();
    }

    /**
     * Complete violation (change status to selesai).
     */
    public function complete(Pelanggaran $pelanggaran): Pelanggaran
    {
        $pelanggaran->update([
            'status' => Pelanggaran::STATUS_SELESAI,
        ]);

        return $pelanggaran->fresh();
    }

    /**
     * Delete violation.
     */
    public function delete(Pelanggaran $pelanggaran): bool
    {
        return $pelanggaran->delete();
    }

    /**
     * Get violation statistics.
     */
    public function getStatistics(?string $from = null, ?string $to = null): array
    {
        $query = Pelanggaran::query();

        if ($from && $to) {
            $query->byDateRange($from, $to);
        }

        $total = $query->count();

        // By jenis
        $byJenis = Pelanggaran::selectRaw('jenis, count(*) as total')
            ->when($from && $to, fn ($q) => $q->byDateRange($from, $to))
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        // By status
        $byStatus = Pelanggaran::selectRaw('status, count(*) as total')
            ->when($from && $to, fn ($q) => $q->byDateRange($from, $to))
            ->groupBy('status')
            ->pluck('total', 'status');

        // By kategori
        $byKategori = Pelanggaran::selectRaw('kategori, count(*) as total')
            ->when($from && $to, fn ($q) => $q->byDateRange($from, $to))
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        // Top santri with violations
        $topSantri = Pelanggaran::selectRaw('santri_id, count(*) as total')
            ->when($from && $to, fn ($q) => $q->byDateRange($from, $to))
            ->groupBy('santri_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('santri')
            ->get();

        return [
            'total' => $total,
            'by_jenis' => $byJenis,
            'by_status' => $byStatus,
            'by_kategori' => $byKategori,
            'top_santri' => $topSantri,
        ];
    }
}
