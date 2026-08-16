<?php

namespace App\Services;

use App\Models\MonitoringKarakter;
use Illuminate\Support\Collection;

class MonitoringKarakterService
{
    public function getAll(): Collection
    {
        return MonitoringKarakter::with(['santri', 'dinilaiOleh'])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function getBySantri(int $santriId): Collection
    {
        return MonitoringKarakter::where('santri_id', $santriId)
            ->with('dinilaiOleh')
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function getByAspek(string $aspek): Collection
    {
        return MonitoringKarakter::byAspek($aspek)
            ->with(['santri', 'dinilaiOleh'])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function create(array $data): MonitoringKarakter
    {
        $data['tenant_id'] = tenant_id();
        $data['dinilai_oleh'] = auth()->id();
        $data['predikat'] = MonitoringKarakter::hitungPredikat($data['skor'] ?? 0);
        $data['tanggal'] = $data['tanggal'] ?? now()->toDateString();

        return MonitoringKarakter::create($data);
    }

    public function update(MonitoringKarakter $monitoring, array $data): MonitoringKarakter
    {
        if (isset($data['skor'])) {
            $data['predikat'] = MonitoringKarakter::hitungPredikat($data['skor']);
        }

        $monitoring->update($data);
        return $monitoring->fresh();
    }

    public function delete(MonitoringKarakter $monitoring): bool
    {
        return $monitoring->delete();
    }

    public function getRekapBySantri(int $santriId): array
    {
        $records = MonitoringKarakter::where('santri_id', $santriId)->get();

        $rekap = [];
        foreach (MonitoringKarakter::ASPEK_OPTIONS as $aspek) {
            $aspekRecords = $records->where('aspek', $aspek);
            $rekap[$aspek] = [
                'total' => $aspekRecords->count(),
                'rata_rata' => $aspekRecords->avg('skor') ?? 0,
                'terakhir' => $aspekRecords->sortByDesc('tanggal')->first(),
            ];
        }

        return $rekap;
    }
}
