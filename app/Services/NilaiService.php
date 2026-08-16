<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Role;
use App\Models\Subject;
use App\Models\UstadzKelas;
use Illuminate\Support\Facades\DB;

class NilaiService
{
    /**
     * Resolve the canonical UstadzKelas assignment for a given kelas+subject+program.
     * Returns null if no assignment exists.
     */
    public function resolveUstadzKelas(int $kelasId, int $subjectId, int $programId): ?UstadzKelas
    {
        return UstadzKelas::where('kelas_id', $kelasId)
            ->where('subject_id', $subjectId)
            ->where('program_id', $programId)
            ->first();
    }

    /**
     * Resolve UstadzKelas from authenticated user context (ustadz users only).
     */
    public function resolveUstadzKelasForUser(\App\Models\User $user, int $kelasId, int $subjectId, int $programId): ?UstadzKelas
    {
        if (! $user->isUstadz() || ! $user->ustadz) {
            return null;
        }

        return $user->ustadz->ustadzKelas()
            ->where('kelas_id', $kelasId)
            ->where('subject_id', $subjectId)
            ->where('program_id', $programId)
            ->first();
    }

    /**
     * Bulk upsert nilai records for a kelas+subject on a given date.
     * Access must already be authorized before calling this method.
     *
     * @param  string  $assessmentType  Active assessment type code from the registry.
     */
    public function bulkUpsert(
        array $records,
        Kelas $kelas,
        Subject $subject,
        string $tanggal,
        int $programId,
        ?UstadzKelas $ustadzKelas,
        string $assessmentType
    ): void {
        DB::transaction(function () use ($records, $kelas, $subject, $tanggal, $programId, $ustadzKelas, $assessmentType) {
            foreach ($records as $row) {
                Nilai::updateOrCreate(
                    [
                        'santri_id'       => $row['santri_id'],
                        'subject_id'      => $subject->id,
                        'kelas_id'        => $kelas->id,
                        'program_id'      => $programId,
                        'tanggal'         => $tanggal,
                        'assessment_type' => $assessmentType,
                    ],
                    [
                        'tenant_id'       => tenant_id(),
                        'ustadz_kelas_id' => $ustadzKelas?->id,
                        'materi'          => $row['materi'] ?? null,
                        'nilai'           => $row['nilai'] ?? null,
                        'catatan'         => $row['catatan'] ?? null,
                    ]
                );
            }
        });
    }

    /**
     * Return kelas list scoped to what the given user is allowed to grade.
     * Admin: all kelas. Ustadz: only their ustadz_kelas assignments.
     */
    public function accessibleKelas(\App\Models\User $user, int $programId): \Illuminate\Support\Collection
    {
        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return Kelas::with('subjects')->where('program_id', $programId)->orderBy('name')->get();
        }

        if ($user->ustadz) {
            $kelasIds = $user->ustadz->ustadzKelas()
                ->where('program_id', $programId)
                ->pluck('kelas_id');

            return Kelas::with(['subjects' => function ($q) use ($user, $programId) {
                $subjectIds = $user->ustadz->ustadzKelas()
                    ->where('program_id', $programId)
                    ->pluck('subject_id');
                $q->whereIn('subjects.id', $subjectIds);
            }])
                ->whereIn('id', $kelasIds)
                ->orderBy('name')
                ->get();
        }

        return collect();
    }
}
