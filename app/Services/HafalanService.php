<?php

namespace App\Services;

use App\Models\HafalanQuran;
use App\Models\HafalanKitab;
use App\Models\Santri;
use App\Models\UstadzKelas;
use Illuminate\Support\Collection;

class HafalanService
{
    /**
     * Get hafalan quran records for the authenticated user.
     */
    public function getHafalanQuranRecords(): Collection
    {
        $user = auth()->user();

        $query = HafalanQuran::with(['santri.kelas', 'ustadzKelas.ustadz.user'])
            ->orderBy('tanggal', 'desc');

        // Relation-based filtering: USER -> Ustadz -> UstadzKelas -> HafalanQuran
        $query->when($user->ustadz, function ($query) use ($user) {
            // Ustadz: only records assigned to them via ustadz_kelas
            $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
            return $query->whereIn('ustadz_kelas_id', $assignedIds);
        });

        return $query->get();
    }

    /**
     * Get hafalan kitab records for the authenticated user.
     */
    public function getHafalanKitabRecords(): Collection
    {
        $user = auth()->user();

        $query = HafalanKitab::with(['santri.kelas', 'ustadzKelas.ustadz.user'])
            ->orderBy('tanggal', 'desc');

        // Relation-based filtering: USER -> Ustadz -> UstadzKelas -> HafalanKitab
        $query->when($user->ustadz, function ($query) use ($user) {
            // Ustadz: only records assigned to them via ustadz_kelas
            $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
            return $query->whereIn('ustadz_kelas_id', $assignedIds);
        });

        return $query->get();
    }

    /**
     * Get list of active santri for hafalan forms.
     */
    public function getSantriList(): Collection
    {
        return Santri::where('status', 'active')->orderBy('name')->get();
    }

    /**
     * Store a new hafalan quran record.
     */
    public function storeHafalanQuran(array $data): HafalanQuran
    {
        $data['tenant_id'] = tenant_id();
        return HafalanQuran::create($data);
    }

    /**
     * Store a new hafalan kitab record.
     */
    public function storeHafalanKitab(array $data): HafalanKitab
    {
        $data['tenant_id'] = tenant_id();
        return HafalanKitab::create($data);
    }

    /**
     * Get hafalan quran progress for a specific santri.
     */
    public function getHafalanQuranProgress(int $santriId): Collection
    {
        $user = auth()->user();

        $query = HafalanQuran::with(['santri.kelas', 'ustadzKelas.ustadz.user'])
            ->where('santri_id', $santriId)
            ->orderBy('tanggal', 'desc');

        // Apply same filtering as main query
        $query->when($user->ustadz, function ($query) use ($user) {
            $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
            return $query->whereIn('ustadz_kelas_id', $assignedIds);
        });

        return $query->get();
    }

    /**
     * Get hafalan kitab progress for a specific santri.
     */
    public function getHafalanKitabProgress(int $santriId): Collection
    {
        $user = auth()->user();

        $query = HafalanKitab::with(['santri.kelas', 'ustadzKelas.ustadz.user'])
            ->where('santri_id', $santriId)
            ->orderBy('tanggal', 'desc');

        // Apply same filtering as main query
        $query->when($user->ustadz, function ($query) use ($user) {
            $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
            return $query->whereIn('ustadz_kelas_id', $assignedIds);
        });

        return $query->get();
    }
}
