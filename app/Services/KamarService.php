<?php

namespace App\Services;

use App\Models\Kamar;
use App\Models\Santri;
use Illuminate\Support\Collection;

class KamarService
{
    /**
     * Get all kamar with occupancy count.
     */
    public function getAll(): Collection
    {
        return Kamar::withCount([
                'santri as terisi' => fn ($query) => $query->whereNotNull('kamar_id')
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active kamar only.
     */
    public function getActive(): Collection
    {
        return Kamar::aktif()
            ->withCount([
                'santri as terisi' => fn ($query) => $query->whereNotNull('kamar_id')
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get kamar with available capacity.
     */
    public function getAvailable(): Collection
    {
        return Kamar::aktif()
            ->withCount([
                'santri as terisi' => fn ($query) => $query->whereNotNull('kamar_id')
            ])
            ->get()
            ->filter(fn ($kamar) => $kamar->sisa_kapasitas > 0)
            ->values();
    }

    /**
     * Get kamar with occupants.
     */
    public function getWithOccupants(int $kamarId): ?Kamar
    {
        return Kamar::with([
            'santri' => fn ($q) => $q->whereNotNull('kamar_id')->orderBy('name'),
        ])->find($kamarId);
    }

    /**
     * Create new kamar.
     */
    public function create(array $data): Kamar
    {
        $data['tenant_id'] = tenant_id();
        $data['status'] = $data['status'] ?? Kamar::STATUS_AKTIF;

        return Kamar::create($data);
    }

    /**
     * Update kamar.
     */
    public function update(Kamar $kamar, array $data): Kamar
    {
        $kamar->update($data);
        return $kamar->fresh();
    }

    /**
     * Delete kamar if empty.
     */
    public function delete(Kamar $kamar): bool
    {
        if ($kamar->santri()->whereNotNull('kamar_id')->count() > 0) {
            throw new \RuntimeException('Kamar masih memiliki penghuni.');
        }

        return $kamar->delete();
    }

    /**
     * Get occupancy statistics.
     */
    public function getStatistics(): array
    {
        $total = Kamar::count();
        $aktif = Kamar::aktif()->count();
        $totalKapasitas = Kamar::sum('kapasitas') ?? 0;
        $totalTerisi = Santri::whereNotNull('kamar_id')->count();

        return [
            'total_kamar' => $total,
            'kamar_aktif' => $aktif,
            'total_kapasitas' => $totalKapasitas,
            'total_terisi' => $totalTerisi,
            'total_tersedia' => max(0, $totalKapasitas - $totalTerisi),
            'tingkat_okupansi' => $totalKapasitas > 0
                ? round(($totalTerisi / $totalKapasitas) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get santri without room assignment.
     */
    public function getSantriWithoutRoom(): Collection
    {
        return Santri::where('status', 'active')
            ->whereNull('kamar_id')
            ->orderBy('name')
            ->get();
    }
}
