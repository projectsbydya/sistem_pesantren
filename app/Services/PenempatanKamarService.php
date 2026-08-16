<?php

namespace App\Services;

use App\Models\Kamar;
use App\Models\PenempatanKamar;
use App\Models\Santri;
use Illuminate\Support\Collection;

class PenempatanKamarService
{
    /**
     * Get current placements (where tanggal_keluar is null).
     */
    public function getCurrent(): Collection
    {
        return PenempatanKamar::whereNull('tanggal_keluar')
            ->with(['santri', 'kamar'])
            ->orderBy('tanggal_masuk', 'desc')
            ->get();
    }

    /**
     * Get current placements by kamar.
     */
    public function getCurrentByKamar(int $kamarId): Collection
    {
        return PenempatanKamar::where('kamar_id', $kamarId)
            ->whereNull('tanggal_keluar')
            ->with('santri')
            ->orderBy('tanggal_masuk', 'desc')
            ->get();
    }

    /**
     * Get current placement by santri.
     */
    public function getCurrentBySantri(int $santriId): ?PenempatanKamar
    {
        return PenempatanKamar::where('santri_id', $santriId)
            ->whereNull('tanggal_keluar')
            ->with('kamar')
            ->latest('tanggal_masuk')
            ->first();
    }

    /**
     * Assign santri to kamar.
     */
    public function assign(array $data): PenempatanKamar
    {
        $kamar = Kamar::findOrFail($data['kamar_id']);

        if ($kamar->is_penuh) {
            throw new \RuntimeException('Kamar sudah penuh.');
        }

        $santri = Santri::findOrFail($data['santri_id']);

        // Close any previous current placement history before creating a new one.
        PenempatanKamar::where('santri_id', $santri->id)
            ->whereNull('tanggal_keluar')
            ->update([
                'tanggal_keluar' => $data['tanggal_masuk'] ?? now()->toDateString(),
            ]);

        $data['tenant_id'] = tenant_id();
        $data['tanggal_masuk'] = $data['tanggal_masuk'] ?? now()->toDateString();

        $placement = PenempatanKamar::create($data);

        // Current state is always kept on the santri record.
        $santri->update(['kamar_id' => $data['kamar_id']]);

        return $placement;
    }

    /**
     * Move santri to different kamar (creates mutation record).
     */
    public function move(int $santriId, int $kamarTujuanId, string $alasan, ?string $keterangan = null): array
    {
        $currentPlacement = PenempatanKamar::where('santri_id', $santriId)
            ->whereNull('tanggal_keluar')
            ->latest('tanggal_masuk')
            ->first();

        $kamarTujuan = Kamar::findOrFail($kamarTujuanId);

        if ($kamarTujuan->is_penuh) {
            throw new \RuntimeException('Kamar tujuan sudah penuh.');
        }

        $kamarAsalId = $currentPlacement?->kamar_id;

        // assign() will close any previous placement and update santri.kamar_id.
        $newPlacement = $this->assign([
            'santri_id' => $santriId,
            'kamar_id' => $kamarTujuanId,
            'keterangan' => $keterangan,
        ]);

        // Record the movement in the mutation history table.
        $mutasi = null;
        if ($kamarAsalId) {
            $mutasi = \App\Models\MutasiKamar::create([
                'tenant_id' => tenant_id(),
                'santri_id' => $santriId,
                'kamar_asal_id' => $kamarAsalId,
                'kamar_tujuan_id' => $kamarTujuanId,
                'tanggal_mutasi' => now()->toDateString(),
                'alasan' => $alasan,
                'keterangan' => $keterangan,
                'processed_by' => auth()->id(),
            ]);
        }

        return [
            'placement' => $newPlacement,
            'mutation' => $mutasi,
        ];
    }

    /**
     * Remove santri from kamar (checkout).
     */
    public function checkout(int $santriId, ?string $keterangan = null): ?PenempatanKamar
    {
        $placement = PenempatanKamar::where('santri_id', $santriId)
            ->whereNull('tanggal_keluar')
            ->latest('tanggal_masuk')
            ->first();

        if (!$placement) {
            return null;
        }

        $placement->update([
            'tanggal_keluar' => now()->toDateString(),
            'keterangan' => $keterangan ?: $placement->keterangan,
        ]);

        Santri::where('id', $santriId)->update(['kamar_id' => null]);

        return $placement;
    }

    /**
     * Get placement history for santri.
     */
    public function getHistory(int $santriId): Collection
    {
        return PenempatanKamar::where('santri_id', $santriId)
            ->with('kamar')
            ->orderBy('tanggal_masuk', 'desc')
            ->get();
    }

    /**
     * Bulk assign santri to kamar.
     */
    public function bulkAssign(array $santriIds, int $kamarId, ?string $tanggalMasuk = null): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        $kamar = Kamar::findOrFail($kamarId);
        $availableSlots = $kamar->sisa_kapasitas;

        foreach ($santriIds as $index => $santriId) {
            if ($index >= $availableSlots) {
                $results['failed'][] = [
                    'santri_id' => $santriId,
                    'reason' => 'Kamar penuh',
                ];
                continue;
            }

            try {
                $this->assign([
                    'santri_id' => $santriId,
                    'kamar_id' => $kamarId,
                    'tanggal_masuk' => $tanggalMasuk,
                ]);
                $results['success'][] = $santriId;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'santri_id' => $santriId,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
