<?php

namespace App\Services;

use App\Models\Elearning;
use App\Models\Program;
use App\Models\Role;
use App\Models\UstadzKelas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ElearningService
{
    /**
     * Resolve the UstadzKelas for the acting user, or fall back to a provided
     * ustadz_kelas_id (admin override).
     */
    public function resolveUstadzKelas(
        \App\Models\User $user,
        int $kelasId,
        int $subjectId,
        string $programSlug,
        ?int $overrideUstadzKelasId = null
    ): ?UstadzKelas {
        if ($user->hasRole(Role::TENANT_ADMIN) && $overrideUstadzKelasId) {
            return UstadzKelas::find($overrideUstadzKelasId);
        }

        if ($user->ustadz) {
            $program = Program::where('slug', $programSlug)->first();

            return $user->ustadz->ustadzKelas()
                ->where('kelas_id', $kelasId)
                ->where('subject_id', $subjectId)
                ->when($program, fn ($q) => $q->where('program_id', $program->id))
                ->first();
        }

        return null;
    }

    /**
     * Store a new elearning material, handling optional file upload.
     */
    public function store(array $data, string $programSlug, ?UploadedFile $file, ?UstadzKelas $ustadzKelas): Elearning
    {
        if ($file) {
            $data['file_path'] = $file->store("elearning/{$programSlug}", 'public');
        }

        $data['ustadz_kelas_id'] = $ustadzKelas?->id;

        unset($data['file']);

        return Elearning::create($data);
    }

    /**
     * Delete a material and its associated file from storage.
     */
    public function delete(Elearning $elearning): void
    {
        if ($elearning->file_path) {
            Storage::disk('public')->delete($elearning->file_path);
        }

        $elearning->delete();
    }

    /**
     * Return elearning query scoped to what the user may see.
     * - Admin   : all materials for the program
     * - Ustadz  : only materials from their own ustadz_kelas assignments
     * - Santri  : materials for their kelas OR global materials (kelas_id null)
     * - Parent  : materials for kelas of any of their children OR global
     */
    public function accessibleQuery(\App\Models\User $user, string $programSlug): \Illuminate\Database\Eloquent\Builder
    {
        $query = Elearning::whereHas('program', fn ($q) => $q->where('slug', $programSlug))
            ->with(['ustadzKelas.ustadz.user', 'subject', 'kelas']);

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return $query;
        }

        if ($user->ustadz) {
            $ids = $user->ustadz->ustadzKelas()
                ->whereHas('program', fn ($q) => $q->where('slug', $programSlug))
                ->pluck('id');

            return $query->whereIn('ustadz_kelas_id', $ids);
        }

        if ($user->santri) {
            $kelasId = $user->santri->kelas_id;
            return $query->where(function ($q) use ($kelasId) {
                $q->whereNull('kelas_id')
                  ->orWhere('kelas_id', $kelasId);
            });
        }

        if ($user->parent) {
            $childKelasIds = $user->parent->santri()->pluck('kelas_id')->filter()->all();
            return $query->where(function ($q) use ($childKelasIds) {
                $q->whereNull('kelas_id')
                  ->orWhereIn('kelas_id', $childKelasIds);
            });
        }

        // Fallback: return nothing
        return $query->whereRaw('1 = 0');
    }

    /**
     * Return UstadzKelas list visible to the user for a given program.
     * Admin: all ustadz_kelas in the program.
     * Ustadz: only their own assignments.
     */
    public function accessibleUstadzKelas(\App\Models\User $user, string $programSlug): \Illuminate\Database\Eloquent\Collection
    {
        $query = UstadzKelas::whereHas('program', fn ($q) => $q->where('slug', $programSlug))
            ->with(['ustadz.user', 'kelas', 'subject']);

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return $query->get();
        }

        if ($user->ustadz) {
            return $user->ustadz->ustadzKelas()
                ->whereHas('program', fn ($q) => $q->where('slug', $programSlug))
                ->with(['kelas', 'subject'])
                ->get();
        }

        return collect();
    }
}
