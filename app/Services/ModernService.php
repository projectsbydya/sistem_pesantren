<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Muhadatsah;
use App\Models\Muhadhoroh;
use App\Models\MuhadhorohTheme;
use App\Models\PlacementTest;
use App\Models\PlacementTestLevel;
use App\Models\PlacementTestResult;
use App\Models\Program;
use App\Models\Santri;
use App\Models\SantriProgram;
use App\Models\Vocabulary;
use Illuminate\Support\Collection;

class ModernService
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

    public function getSantriListForProgram(Program $program): Collection
    {
        $query = Santri::where('status', 'active')
            ->whereHas('programs', fn ($q) => $q->where('program_id', $program->id))
            ->orderBy('name');

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
     * Scope a query so students and parents only see their own / their children's records.
     * Returns null when the current user is not a readonly viewer.
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
     * Apply a readonly scope to an Eloquent query that has a santri_id column.
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

    private function filterPlacementResultsBySantri($query, ?array $ids): void
    {
        if ($ids === null) {
            return;
        }

        if (empty($ids)) {
            $query->whereRaw('0 = 1');
        } else {
            $query->whereIn('santri_id', $ids);
        }
    }

    // =========================================================================
    // Vocabulary — Unified Entity (arabic|english)
    // =========================================================================

    public function getVocabularyRecords(Program $program, ?string $type = null, ?int $kelasId = null): Collection
    {
        $query = Vocabulary::with(['santri', 'program'])
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);
        $this->filterByKelas($query, $program, $kelasId);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getVocabularyForSantri(int $santriId, Program $program, ?string $type = null): Collection
    {
        $query = Vocabulary::with(['program'])
            ->bySantri($santriId)
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function storeVocabulary(array $data): Vocabulary
    {
        $data['tenant_id'] = tenant_id();
        return Vocabulary::create($data);
    }

    public function updateVocabulary(Vocabulary $vocabulary, array $data): Vocabulary
    {
        $vocabulary->update($data);
        return $vocabulary->fresh();
    }

    public function deleteVocabulary(Vocabulary $vocabulary): bool
    {
        return $vocabulary->delete();
    }

    // =========================================================================
    // Muhadatsah — Unified Entity (arabic|english)
    // =========================================================================

    public function getMuhadatsahRecords(Program $program, ?string $type = null, ?int $kelasId = null): Collection
    {
        $query = Muhadatsah::with(['santri', 'program'])
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);
        $this->filterByKelas($query, $program, $kelasId);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getMuhadatsahForSantri(int $santriId, Program $program, ?string $type = null): Collection
    {
        $query = Muhadatsah::with(['program'])
            ->bySantri($santriId)
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function storeMuhadatsah(array $data): Muhadatsah
    {
        $data['tenant_id'] = tenant_id();
        return Muhadatsah::create($data);
    }

    public function updateMuhadatsah(Muhadatsah $muhadatsah, array $data): Muhadatsah
    {
        $muhadatsah->update($data);
        return $muhadatsah->fresh();
    }

    public function deleteMuhadatsah(Muhadatsah $muhadatsah): bool
    {
        return $muhadatsah->delete();
    }

    // =========================================================================
    // Muhadhoroh — Unified Entity (muhadhoroh|public-speaking)
    // =========================================================================

    public function getMuhadhorohThemes(Program $program): Collection
    {
        return MuhadhorohTheme::active()
            ->byProgram($program->id)
            ->orderBy('name')
            ->get();
    }

    public function storeMuhadhorohTheme(array $data): MuhadhorohTheme
    {
        $data['tenant_id'] = tenant_id();
        return MuhadhorohTheme::create($data);
    }

    public function getMuhadhorohRecords(Program $program, ?string $type = null, ?int $kelasId = null): Collection
    {
        $query = Muhadhoroh::with(['santri', 'program', 'theme'])
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);
        $this->filterByKelas($query, $program, $kelasId);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getMuhadhorohForSantri(int $santriId, Program $program, ?string $type = null): Collection
    {
        $query = Muhadhoroh::with(['program', 'theme'])
            ->bySantri($santriId)
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        $this->applyReadonlyScope($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function storeMuhadhoroh(array $data): Muhadhoroh
    {
        $data['tenant_id'] = tenant_id();
        return Muhadhoroh::create($data);
    }

    public function updateMuhadhoroh(Muhadhoroh $muhadhoroh, array $data): Muhadhoroh
    {
        $muhadhoroh->update($data);
        return $muhadhoroh->fresh();
    }

    public function deleteMuhadhoroh(Muhadhoroh $muhadhoroh): bool
    {
        return $muhadhoroh->delete();
    }

    // =========================================================================
    // Placement Test — Test header (one test → many results)
    // =========================================================================

    public function getPlacementTestLevels(Program $program): Collection
    {
        return PlacementTestLevel::active()
            ->byProgram($program->id)
            ->orderBy('min_score', 'asc')
            ->get();
    }

    public function resolveLevelForScore(?int $score, int $programId): ?int
    {
        if ($score === null) {
            return null;
        }

        return PlacementTestLevel::active()
            ->where('tenant_id', tenant_id())
            ->where('program_id', $programId)
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->orderBy('min_score', 'asc')
            ->value('id');
    }

    public function getPlacementTestRecords(Program $program, ?string $type = null): Collection
    {
        $ids = $this->accessibleSantriIds();

        $query = PlacementTest::with([
                'results' => fn ($q) => $this->filterPlacementResultsBySantri($q, $ids),
                'results.santri',
                'results.level',
                'program',
            ])
            ->byProgram($program->id);

        if ($type !== null) {
            $query->byType($type);
        }

        if ($ids !== null) {
            if (empty($ids)) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('results', fn ($q) => $q->whereIn('santri_id', $ids));
            }
        }

        return $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();
    }

    public function getPlacementTestResultsForSantri(int $santriId, Program $program, ?string $type = null): Collection
    {
        $query = PlacementTestResult::with(['test', 'level', 'program'])
            ->bySantri($santriId)
            ->byProgram($program->id);

        if ($type !== null) {
            $query->whereHas('test', fn ($q) => $q->byType($type));
        }

        $this->applyReadonlyScope($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function storePlacementTest(array $data): PlacementTest
    {
        return PlacementTest::create([
            'tenant_id' => tenant_id(),
            'program_id' => $data['program_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'date' => $data['date'] ?? null,
            'description' => $data['description'] ?? null,
            'max_score' => $data['max_score'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function updatePlacementTest(PlacementTest $placementTest, array $data): PlacementTest
    {
        $placementTest->update([
            'title' => $data['title'],
            'date' => $data['date'] ?? null,
            'description' => $data['description'] ?? null,
            'max_score' => $data['max_score'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $placementTest->fresh();
    }

    public function deletePlacementTest(PlacementTest $placementTest): bool
    {
        return $placementTest->delete();
    }

    // =========================================================================
    // Placement Test Results
    // =========================================================================

    public function storePlacementTestResult(array $data): PlacementTestResult
    {
        $levelId = $this->resolveLevelForScore($data['score'] ?? null, $data['program_id']);

        return PlacementTestResult::create([
            'tenant_id' => tenant_id(),
            'program_id' => $data['program_id'],
            'placement_test_id' => $data['placement_test_id'],
            'santri_id' => $data['santri_id'],
            'score' => $data['score'] ?? null,
            'level_id' => $levelId,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function updatePlacementTestResult(PlacementTestResult $result, array $data): PlacementTestResult
    {
        $result->update([
            'score' => $data['score'] ?? null,
            'level_id' => $this->resolveLevelForScore($data['score'] ?? null, $result->program_id),
            'notes' => $data['notes'] ?? null,
        ]);

        return $result->fresh();
    }

    public function deletePlacementTestResult(PlacementTestResult $result): bool
    {
        return $result->delete();
    }
}
