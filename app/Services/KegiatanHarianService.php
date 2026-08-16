<?php

namespace App\Services;

use App\Models\KegiatanHarian;
use Illuminate\Support\Collection;

class KegiatanHarianService
{
    public function getAll(): Collection
    {
        return KegiatanHarian::with(['santri', 'dicatatOleh'])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function getBySantri(int $santriId): Collection
    {
        return KegiatanHarian::where('santri_id', $santriId)
            ->with('dicatatOleh')
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function getByDate(string $date): Collection
    {
        return KegiatanHarian::byDate($date)
            ->with(['santri', 'dicatatOleh'])
            ->get();
    }

    public function getByJenis(string $jenis): Collection
    {
        return KegiatanHarian::byJenis($jenis)
            ->with(['santri', 'dicatatOleh'])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function create(array $data): KegiatanHarian
    {
        $data['tenant_id'] = tenant_id();
        $data['dicatat_oleh'] = auth()->id();
        $data['tanggal'] = $data['tanggal'] ?? now()->toDateString();

        return KegiatanHarian::create($data);
    }

    public function update(KegiatanHarian $kegiatan, array $data): KegiatanHarian
    {
        $kegiatan->update($data);
        return $kegiatan->fresh();
    }

    public function markAsDone(KegiatanHarian $kegiatan, ?string $catatan = null): KegiatanHarian
    {
        $kegiatan->update([
            'status' => KegiatanHarian::STATUS_DILAKSANAKAN,
            'catatan' => $catatan,
        ]);
        return $kegiatan->fresh();
    }

    public function markAsMissed(KegiatanHarian $kegiatan, ?string $catatan = null): KegiatanHarian
    {
        $kegiatan->update([
            'status' => KegiatanHarian::STATUS_TIDAK_DILAKSANAKAN,
            'catatan' => $catatan,
        ]);
        return $kegiatan->fresh();
    }

    public function delete(KegiatanHarian $kegiatan): bool
    {
        return $kegiatan->delete();
    }

    public function bulkCreate(array $santriIds, array $data): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($santriIds as $santriId) {
            try {
                $recordData = array_merge($data, ['santri_id' => $santriId]);
                $this->create($recordData);
                $results['success'][] = $santriId;
            } catch (\Exception $e) {
                $results['failed'][] = ['santri_id' => $santriId, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }
}
