<?php

namespace App\Policies;

use App\Models\DiniyahAssessment;
use App\Models\UstadzKelas;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Database\Eloquent\Model;

/**
 * DiniyahAssessmentPolicy — Unified Authorization for Assessment Management
 *
 * ARCHITECTURE FROZEN: Single policy for unified DiniyahAssessment entity.
 * Replaces legacy: DiniyahNilaiKeagamaanPolicy, DiniyahNilaiAkhlakPolicy
 *
 * @frozen 2026-06-14
 */
class DiniyahAssessmentPolicy
{
    /**
     * Ensure all operations respect tenant boundaries.
     */
    private function belongsToTenant(Model $record): bool
    {
        return (int) $record->tenant_id === (int) TenantService::getTenantId();
    }

    public function viewAny(User $user): bool
    {
        return $user->can('diniyah.view') || $user->hasRole(['ustadz', 'admin']);
    }

    public function view(User $user, DiniyahAssessment $assessment): bool
    {
        return $this->belongsToTenant($assessment)
            && ($user->can('diniyah.view') || $user->hasRole(['ustadz', 'admin']));
    }

    public function create(User $user): bool
    {
        return $user->can('diniyah.create') || $user->hasRole(['ustadz', 'admin']);
    }

    public function update(User $user, DiniyahAssessment $assessment): bool
    {
        return $this->belongsToTenant($assessment)
            && ($user->can('diniyah.edit') || $user->hasRole(['ustadz', 'admin']));
    }

    public function delete(User $user, DiniyahAssessment $assessment): bool
    {
        return $this->belongsToTenant($assessment)
            && ($user->can('diniyah.delete') || $user->hasRole('admin'));
    }

    public function recordFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        if ($user->isSuperAdmin()) return false;
        if ((int) $user->tenant_id !== (int) $ustadzKelas->tenant_id) return false;

        if ($user->ustadz) {
            return $user->ustadz->ustadzKelas()->where('id', $ustadzKelas->id)->exists();
        }

        return $user->can('diniyah.create') || $user->hasRole(['ustadz', 'admin']);
    }

    public function rekap(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function riwayat(User $user, int $santriId): bool
    {
        return $this->viewAny($user);
    }
}
