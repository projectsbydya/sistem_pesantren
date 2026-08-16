<?php

namespace App\Services;

use App\Models\Pelanggaran;
use App\Models\Sanksi;
use Illuminate\Support\Collection;

class SanksiService
{
    /**
     * Get all sanctions.
     */
    public function getAll(): Collection
    {
        return Sanksi::with([
            'pelanggaran.santri',
            'diberikanOleh',
        ])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get sanctions by status.
     */
    public function getByStatus(string $status): Collection
    {
        return Sanksi::byStatus($status)
            ->with([
                'pelanggaran.santri',
                'diberikanOleh',
            ])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get sanctions by jenis.
     */
    public function getByJenis(string $jenis): Collection
    {
        return Sanksi::byJenis($jenis)
            ->with([
                'pelanggaran.santri',
                'diberikanOleh',
            ])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get sanctions by santri.
     */
    public function getBySantri(int $santriId): Collection
    {
        return Sanksi::whereHas('pelanggaran', fn ($q) => $q->where('santri_id', $santriId))
            ->with([
                'pelanggaran',
                'diberikanOleh',
            ])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get active sanctions for santri.
     */
    public function getActiveBySantri(int $santriId): Collection
    {
        return Sanksi::active()
            ->whereHas('pelanggaran', fn ($q) => $q->where('santri_id', $santriId))
            ->with([
                'pelanggaran',
                'diberikanOleh',
            ])
            ->get();
    }

    /**
     * Create sanction for a violation.
     */
    public function create(array $data): Sanksi
    {
        $data['tenant_id'] = tenant_id();
        $data['diberikan_oleh'] = auth()->id();
        $data['status'] = $data['status'] ?? Sanksi::STATUS_AKTIF;
        $data['tanggal_mulai'] = $data['tanggal_mulai'] ?? now()->toDateString();

        $sanksi = Sanksi::create($data);

        // Update violation status if needed
        if (isset($data['pelanggaran_id'])) {
            $pelanggaran = Pelanggaran::find($data['pelanggaran_id']);
            if ($pelanggaran && $pelanggaran->status === Pelanggaran::STATUS_PENDING) {
                $pelanggaran->update(['status' => Pelanggaran::STATUS_DIPROSES]);
            }
        }

        return $sanksi;
    }

    /**
     * Update sanction.
     */
    public function update(Sanksi $sanksi, array $data): Sanksi
    {
        $sanksi->update($data);
        return $sanksi->fresh();
    }

    /**
     * Complete sanction.
     */
    public function complete(Sanksi $sanksi, ?string $hasilEvaluasi = null): Sanksi
    {
        $sanksi->update([
            'status' => Sanksi::STATUS_SELESAI,
            'hasil_evaluasi' => $hasilEvaluasi,
        ]);

        // Check if all sanctions for this violation are completed
        $pelanggaran = $sanksi->pelanggaran;
        if ($pelanggaran && $pelanggaran->sanksi()->active()->count() === 0) {
            $pelanggaran->update(['status' => Pelanggaran::STATUS_SELESAI]);
        }

        return $sanksi->fresh();
    }

    /**
     * Cancel sanction.
     */
    public function cancel(Sanksi $sanksi, ?string $reason = null): Sanksi
    {
        $sanksi->update([
            'status' => Sanksi::STATUS_DIBATALKAN,
            'hasil_evaluasi' => $reason,
        ]);

        return $sanksi->fresh();
    }

    /**
     * Delete sanction.
     */
    public function delete(Sanksi $sanksi): bool
    {
        return $sanksi->delete();
    }

    /**
     * Get sanction statistics.
     */
    public function getStatistics(?string $from = null, ?string $to = null): array
    {
        $query = Sanksi::query();

        if ($from && $to) {
            $query->whereBetween('tanggal_mulai', [$from, $to]);
        }

        $total = $query->count();

        // By jenis
        $byJenis = Sanksi::selectRaw('jenis, count(*) as total')
            ->when($from && $to, fn ($q) => $q->whereBetween('tanggal_mulai', [$from, $to]))
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        // By status
        $byStatus = Sanksi::selectRaw('status, count(*) as total')
            ->when($from && $to, fn ($q) => $q->whereBetween('tanggal_mulai', [$from, $to]))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Active count
        $activeCount = Sanksi::active()->count();

        // Completed this month
        $completedThisMonth = Sanksi::where('status', Sanksi::STATUS_SELESAI)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        return [
            'total' => $total,
            'by_jenis' => $byJenis,
            'by_status' => $byStatus,
            'active_count' => $activeCount,
            'completed_this_month' => $completedThisMonth,
        ];
    }
}
