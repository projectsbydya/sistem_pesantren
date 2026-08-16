<?php

namespace App\Services;

use App\Models\DiniyahAssessment;
use App\Models\DiniyahHafalan;
use App\Models\DiniyahMonitoring;
use App\Models\Kelas;
use App\Models\Program;
use App\Models\Santri;
use App\Models\SantriProgram;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DiniyahService
{
    // =========================================================================
    // Program resolution
    // =========================================================================

    public function resolveProgram(string $programSlug): Program
    {
        $tenantId = (int) tenant_id();

        return Program::whereHas('tenants', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->where('tenant_programs.is_active', true);
        })->where('slug', $programSlug)->firstOrFail();
    }

    // =========================================================================
    // Santri list helpers
    // =========================================================================

    public function getSantriList(): Collection
    {
        $query = Santri::where('status', 'active')->orderBy('name');

        $this->restrictToAccessibleSantri($query);

        return $query->get();
    }

    /**
     * Build the list of Kelas for a program, each with its enrolled active
     * santri attached as a virtual 'santri' relation.
     *
     * NOTE: Per-program class placement is tracked via the santri_program
     * pivot (SantriProgram::kelas_id), NOT the legacy santri.kelas_id column
     * (which is unused/always null). This must read from that pivot.
     */
    public function getKelasListForProgram(Program $program): Collection
    {
        $accessibleSantriIds = $this->accessibleSantriIds();

        $kelasQuery = Kelas::where('program_id', $program->id)->orderBy('name');

        if ($accessibleSantriIds !== null) {
            if (empty($accessibleSantriIds)) {
                return collect();
            }

            $kelasIds = SantriProgram::where('program_id', $program->id)
                ->whereNotNull('kelas_id')
                ->whereIn('santri_id', $accessibleSantriIds)
                ->distinct()
                ->pluck('kelas_id');

            $kelasQuery->whereIn('id', $kelasIds);
        }

        $kelasList = $kelasQuery->get();

        $santriByKelasQuery = SantriProgram::where('program_id', $program->id)
            ->whereNotNull('kelas_id')
            ->whereIn('kelas_id', $kelasList->pluck('id'))
            ->with(['santri' => fn ($q) => $q->where('status', 'active')]);

        if ($accessibleSantriIds !== null) {
            $santriByKelasQuery->whereIn('santri_id', $accessibleSantriIds);
        }

        $santriByKelas = $santriByKelasQuery->get()->groupBy('kelas_id');

        return $kelasList->map(function (Kelas $kelas) use ($santriByKelas) {
            $santri = ($santriByKelas[$kelas->id] ?? collect())
                ->pluck('santri')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();

            $kelas->setRelation('santri', $santri);

            return $kelas;
        });
    }

    public function getSantriListForProgram(Program $program): Collection
    {
        $query = Santri::where('status', 'active')
            ->whereHas('programs', fn ($q) => $q->where('program_id', $program->id))
            ->orderBy('name');

        $this->restrictToAccessibleSantri($query);

        return $query->get();
    }

    // =========================================================================
    // Generic private methods
    // =========================================================================

    private function applyUstadzScope($query): void
    {
        $user = auth()->user();
        if ($user->ustadz) {
            $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
            $query->whereIn('ustadz_kelas_id', $assignedIds);
        }
    }

    /**
     * Filter a records query down to santri enrolled in the given Kelas for
     * this program. Class placement is tracked via the santri_program pivot
     * (SantriProgram::kelas_id), so we go through Santri::programs().
     */
    private function filterByKelas($query, Program $program, ?int $kelasId): void
    {
        if ($kelasId === null) {
            return;
        }

        $query->whereHas('santri.programs', function ($q) use ($program, $kelasId) {
            $q->where('program_id', $program->id)->where('kelas_id', $kelasId);
        });
    }

    /**
     * Return the santri IDs accessible to the current student/parent user,
     * or null for staff/ustadz where no extra scoping is needed.
     */
    private function accessibleSantriIds(): ?array
    {
        $user = auth()->user();

        if (! $user || ! ($user->isStudent() || $user->isParent())) {
            return null;
        }

        return $user->getAccessibleSantriIds();
    }

    /**
     * Apply a readonly scope to an Eloquent query with a santri_id column.
     */
    private function applyReadonlyScope($query, string $santriColumn = 'santri_id'): void
    {
        $ids = $this->accessibleSantriIds();

        if ($ids === null) {
            return;
        }

        if (empty($ids)) {
            $query->whereRaw('0 = 1');
        } else {
            $query->whereIn($santriColumn, $ids);
        }
    }

    private function restrictToAccessibleSantri($query): void
    {
        $ids = $this->accessibleSantriIds();

        if ($ids === null) {
            return;
        }

        if (empty($ids)) {
            $query->whereRaw('0 = 1');
        } else {
            $query->whereIn('id', $ids);
        }
    }

    private function queryRecords(string $modelClass, Program $program, array $with = []): Collection
    {
        $query = $modelClass::with($with)
            ->where('program_id', $program->id)
            ->orderBy('tanggal', 'desc');
        $this->applyUstadzScope($query);
        return $query->get();
    }

    private function queryForSantri(string $modelClass, int $santriId, Program $program, array $with = []): Collection
    {
        $query = $modelClass::with($with)
            ->where('santri_id', $santriId)
            ->where('program_id', $program->id)
            ->orderBy('tanggal', 'desc');
        $this->applyUstadzScope($query);
        return $query->get();
    }

    private function createRecord(string $modelClass, array $data, bool $withPredikat = false): Model
    {
        $data['tenant_id'] = tenant_id();
        if ($withPredikat && !isset($data['predikat']) && isset($data['nilai'])) {
            $data['predikat'] = $modelClass::hitungPredikat((float) $data['nilai']);
        }
        return $modelClass::create($data);
    }

    // =========================================================================
    // Unified Diniyah Hafalan (Doa, Hadits, Surat)
    // =========================================================================
    // ARCHITECTURE FROZEN: Single entity DiniyahHafalan handles all hafalan types
    // via the 'type' column (doa|hadits|surat). Legacy separate entities removed.
    // =========================================================================

    public function getHafalanRecords(Program $program, ?string $type = null, ?int $kelasId = null): Collection
    {
        $query = DiniyahHafalan::with(['santri.kelas', 'program'])
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);
        $this->filterByKelas($query, $program, $kelasId);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getHafalanProgress(int $santriId, Program $program, ?string $type = null): Collection
    {
        $query = DiniyahHafalan::with(['santri', 'program'])
            ->bySantri($santriId)
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function storeHafalan(array $data): DiniyahHafalan
    {
        $data['tenant_id'] = tenant_id();
        return DiniyahHafalan::create($data);
    }

    // =========================================================================
    // Legacy compatibility methods (redirect to unified entity)
    // =========================================================================

    public function getHafalanDoaRecords(Program $program, ?int $kelasId = null): Collection
    {
        return $this->getHafalanRecords($program, 'doa', $kelasId);
    }

    public function getHafalanDoaProgress(int $santriId, Program $program): Collection
    {
        return $this->getHafalanProgress($santriId, $program, 'doa');
    }

    public function getHafalanHaditsRecords(Program $program, ?int $kelasId = null): Collection
    {
        return $this->getHafalanRecords($program, 'hadits', $kelasId);
    }

    public function getHafalanHaditsProgress(int $santriId, Program $program): Collection
    {
        return $this->getHafalanProgress($santriId, $program, 'hadits');
    }

    public function getHafalanSuratRecords(Program $program, ?int $kelasId = null): Collection
    {
        return $this->getHafalanRecords($program, 'surat', $kelasId);
    }

    public function getHafalanSuratProgress(int $santriId, Program $program): Collection
    {
        return $this->getHafalanProgress($santriId, $program, 'surat');
    }

    // =========================================================================
    // Unified Diniyah Monitoring (Sholat, Adab, Akhlak)
    // =========================================================================
    // ARCHITECTURE FROZEN: Single entity DiniyahMonitoring handles all monitoring
    // via the 'type' column (sholat|adab|akhlak). Legacy separate entities deprecated.
    // =========================================================================

    public function getMonitoringRecords(Program $program, ?string $type = null, ?int $kelasId = null): Collection
    {
        $query = DiniyahMonitoring::with(['santri', 'program'])
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);
        $this->filterByKelas($query, $program, $kelasId);

        return $query->orderBy('date', 'desc')->get();
    }

    public function getMonitoringForSantri(int $santriId, Program $program, ?string $type = null): Collection
    {
        $query = DiniyahMonitoring::with(['santri', 'program'])
            ->bySantri($santriId)
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        return $query->orderBy('date', 'desc')->get();
    }

    public function storeMonitoring(array $data): DiniyahMonitoring
    {
        $data['tenant_id'] = tenant_id();
        return DiniyahMonitoring::create($data);
    }

    public function updateMonitoring(DiniyahMonitoring $record, array $data): DiniyahMonitoring
    {
        $record->update($data);
        return $record;
    }

    public function deleteMonitoring(DiniyahMonitoring $record): bool
    {
        return $record->delete();
    }

    public function getMonitoringRekap(Program $program, string $from, string $to, ?string $type = null): array
    {
        $query = DiniyahMonitoring::byProgram($program->id)
            ->byDateRange($from, $to);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        $records = $query->get();

        $rekap = [
            'total' => $records->count(),
            'by_type' => [],
            'by_status' => [],
            'by_santri' => [],
            'by_aspect' => [],
            'average_score' => null,
        ];

        $rekap['by_aspect'] = $records->groupBy('aspect')->map->count()->toArray();

        foreach (DiniyahMonitoring::TYPES as $t) {
            $rekap['by_type'][$t] = $records->where('type', $t)->count();
        }

        $allStatuses = array_merge(DiniyahMonitoring::SHOLAT_STATUSES, DiniyahMonitoring::AKHLAK_STATUSES);
        foreach ($allStatuses as $status) {
            $rekap['by_status'][$status] = $records->where('status', $status)->count();
        }

        $scores = $records->whereNotNull('score')->pluck('score');
        if ($scores->count() > 0) {
            $rekap['average_score'] = round($scores->avg(), 2);
        }

        return $rekap;
    }

    public function getMonitoringRiwayat(int $santriId, Program $program, ?string $type = null): Collection
    {
        return $this->getMonitoringForSantri($santriId, $program, $type);
    }

    // =========================================================================
    // Legacy compatibility methods (redirect to unified entity)
    // =========================================================================

    public function getMonitoringSholatRecords(Program $program, ?int $kelasId = null): Collection
    {
        return $this->getMonitoringRecords($program, 'sholat', $kelasId);
    }

    public function getMonitoringSholatForSantri(int $santriId, Program $program): Collection
    {
        return $this->getMonitoringForSantri($santriId, $program, 'sholat');
    }

    public function storeMonitoringSholat(array $data): DiniyahMonitoring
    {
        $data['type'] = 'sholat';
        return $this->storeMonitoring($data);
    }

    public function getMonitoringAdabRecords(Program $program, ?int $kelasId = null): Collection
    {
        return $this->getMonitoringRecords($program, 'adab', $kelasId);
    }

    public function getMonitoringAdabForSantri(int $santriId, Program $program): Collection
    {
        return $this->getMonitoringForSantri($santriId, $program, 'adab');
    }

    public function storeMonitoringAdab(array $data): DiniyahMonitoring
    {
        $data['type'] = 'adab';
        return $this->storeMonitoring($data);
    }

    public function getMonitoringAkhlakRecords(Program $program, ?int $kelasId = null): Collection
    {
        return $this->getMonitoringRecords($program, 'akhlak', $kelasId);
    }

    public function getMonitoringAkhlakForSantri(int $santriId, Program $program): Collection
    {
        return $this->getMonitoringForSantri($santriId, $program, 'akhlak');
    }

    public function storeMonitoringAkhlak(array $data): DiniyahMonitoring
    {
        $data['type'] = 'akhlak';
        return $this->storeMonitoring($data);
    }

    // =========================================================================
    // Diniyah Assessment — Unified Entity Methods
    // ARCHITECTURE FROZEN: Replaces DiniyahNilaiKeagamaan and DiniyahNilaiAkhlak
    // =========================================================================

    public function getAssessmentRecords(Program $program, ?string $type = null): Collection
    {
        $query = DiniyahAssessment::with(['santri.kelas', 'program'])->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getAssessmentForSantri(int $santriId, Program $program, ?string $type = null): Collection
    {
        $query = DiniyahAssessment::with(['program'])
            ->byProgram($program->id)
            ->bySantri($santriId);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function storeAssessment(array $data): DiniyahAssessment
    {
        $data['tenant_id'] = tenant_id();
        return DiniyahAssessment::create($data);
    }

    public function updateAssessment(DiniyahAssessment $assessment, array $data): DiniyahAssessment
    {
        $assessment->update($data);
        return $assessment->fresh();
    }

    public function getAssessmentRekap(Program $program, string $from, string $to, ?string $type = null): array
    {
        $query = DiniyahAssessment::byProgram($program->id)
            ->whereBetween('created_at', [$from, $to]);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        $records = $query->get();

        $total = $records->count();
        $averageScore = $total > 0 ? $records->avg('score') : 0;
        $byPredikat = $records->groupBy('predikat')->map->count();

        return [
            'total'        => $total,
            'average_score'=> round($averageScore, 2),
            'by_predikat'  => $byPredikat,
        ];
    }

    public function getAssessmentRiwayat(int $santriId, Program $program, ?string $type = null): Collection
    {
        return $this->getAssessmentForSantri($santriId, $program, $type);
    }

    // =========================================================================
    // Legacy compatibility methods (redirect to unified entity)
    // =========================================================================

    public function getNilaiKeagamaanRecords(Program $program): Collection
    {
        return $this->getAssessmentRecords($program, 'keagamaan');
    }

    public function getNilaiKeagamaanForSantri(int $santriId, Program $program): Collection
    {
        return $this->getAssessmentForSantri($santriId, $program, 'keagamaan');
    }

    public function storeNilaiKeagamaan(array $data): DiniyahAssessment
    {
        $data['type'] = 'keagamaan';
        return $this->storeAssessment($data);
    }

    public function getNilaiAkhlakRecords(Program $program): Collection
    {
        return $this->getAssessmentRecords($program, 'akhlak');
    }

    public function getNilaiAkhlakForSantri(int $santriId, Program $program): Collection
    {
        return $this->getAssessmentForSantri($santriId, $program, 'akhlak');
    }

    public function storeNilaiAkhlak(array $data): DiniyahAssessment
    {
        $data['type'] = 'akhlak';
        return $this->storeAssessment($data);
    }
}
