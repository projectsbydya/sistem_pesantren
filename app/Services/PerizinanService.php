<?php

namespace App\Services;

use App\Models\Perizinan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PerizinanService
{
    public function getAll(): Collection
    {
        return Perizinan::with(['santri', 'diajukanOleh', 'disetujuiOleh'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return Perizinan::byStatus($status)
            ->with(['santri', 'diajukanOleh', 'disetujuiOleh'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return Perizinan::pending()
            ->with(['santri', 'diajukanOleh'])
            ->orderBy('tanggal_mulai', 'asc')
            ->get();
    }

    public function getBySantri(int $santriId): LengthAwarePaginator
    {
        return Perizinan::where('santri_id', $santriId)
            ->with(['diajukanOleh', 'disetujuiOleh'])
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $data): Perizinan
    {
        $data['tenant_id'] = tenant_id();
        $data['diajukan_oleh'] = auth()->id();
        $data['status'] = Perizinan::STATUS_PENDING;

        unset($data['disetujui_oleh'], $data['tanggal_persetujuan'], $data['tanggal_kembali']);

        return Perizinan::create($data);
    }

    public function update(Perizinan $perizinan, array $data): Perizinan
    {
        if ($perizinan->status !== Perizinan::STATUS_PENDING) {
            throw new \RuntimeException('Hanya izinan pending yang dapat diperbarui.');
        }

        // Ownership and workflow fields must not change through this route.
        unset($data['tenant_id'], $data['santri_id'], $data['diajukan_oleh'], $data['status']);
        unset($data['disetujui_oleh'], $data['tanggal_persetujuan'], $data['tanggal_kembali']);

        $perizinan->update($data);

        return $perizinan->fresh();
    }

    public function approve(Perizinan $perizinan, ?string $catatan = null): Perizinan
    {
        if ($perizinan->status !== Perizinan::STATUS_PENDING) {
            throw new \RuntimeException('Izin hanya dapat disetujui saat status pending.');
        }

        $perizinan->update([
            'status' => Perizinan::STATUS_DISETUJUI,
            'disetujui_oleh' => auth()->id(),
            'tanggal_persetujuan' => now(),
            'catatan_keamanan' => $catatan,
        ]);
        return $perizinan->fresh();
    }

    public function reject(Perizinan $perizinan, ?string $alasan = null): Perizinan
    {
        if ($perizinan->status !== Perizinan::STATUS_PENDING) {
            throw new \RuntimeException('Izin hanya dapat ditolak saat status pending.');
        }

        $perizinan->update([
            'status' => Perizinan::STATUS_DITOLAK,
            'disetujui_oleh' => auth()->id(),
            'tanggal_persetujuan' => now(),
            'catatan_keamanan' => $alasan,
        ]);
        return $perizinan->fresh();
    }

    public function recordReturn(Perizinan $perizinan): Perizinan
    {
        if ($perizinan->status !== Perizinan::STATUS_DISETUJUI) {
            throw new \RuntimeException('Izin hanya dapat dicatat kembali saat status disetujui.');
        }

        $perizinan->update([
            'status' => Perizinan::STATUS_KEMBALI,
            'tanggal_kembali' => now(),
        ]);
        return $perizinan->fresh();
    }

    public function delete(Perizinan $perizinan): bool
    {
        if ($perizinan->status !== Perizinan::STATUS_PENDING) {
            throw new \RuntimeException('Hanya izinan pending yang dapat dihapus.');
        }
        return $perizinan->delete();
    }
}
