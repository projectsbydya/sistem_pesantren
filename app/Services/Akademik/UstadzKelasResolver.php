<?php

namespace App\Services\Akademik;

use App\Models\UstadzKelas;
use App\Models\User;

class UstadzKelasResolver
{
    public function resolve(User $user, int $kelasId, int $subjectId): UstadzKelas
    {
        // Kalau ustadz → hanya boleh dari relasinya sendiri
        if ($user->isUstadz() && $user->ustadz) {
            return $user->ustadz->ustadzKelas()
                ->where('kelas_id', $kelasId)
                ->where('subject_id', $subjectId)
                ->firstOrFail();
        }

        // Admin → bebas dalam tenant
        return UstadzKelas::where('kelas_id', $kelasId)
            ->where('subject_id', $subjectId)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();
    }
}