<?php

namespace App\Services;

use Illuminate\Support\Collection;

class AssessmentCalculationService
{
    private AssessmentTypeService $assessmentTypeService;

    public function __construct(AssessmentTypeService $assessmentTypeService)
    {
        $this->assessmentTypeService = $assessmentTypeService;
    }

    /**
     * Get active assessment type configurations for a program from the registry.
     *
     * @return Collection<int, object{
     *     id: int,
     *     code: string,
     *     label: string,
     *     weight: float|null,
     *     sort_order: int,
     *     is_active: bool,
     *     aggregation: string
     * }>
     */
    public function getTypeConfigs(int $programId): Collection
    {
        return $this->assessmentTypeService->getActiveTypes($programId)
            ->map(fn (object $type) => (object) [
                'id'          => $type->id,
                'code'        => $type->code,
                'label'       => $type->label,
                'weight'      => $type->weight,
                'sort_order'  => $type->sort_order,
                'is_active'   => $type->is_active,
                'aggregation' => $type->aggregation,
            ]);
    }

    /**
     * Calculate assessment components from nilai records grouped by type code.
     *
     * Components store the raw score plus the registry weight/contribution when
     * the registry provides one. When the registry does not define a weight,
     * contribution stays null and the caller must fall back to legacy scoring.
     *
     * @param Collection<string, Collection> $nilaiByType
     * @param Collection<int, object> $configs
     * @return array<string, array{
     *     assessment_type_id: int,
     *     assessment_code: string,
     *     assessment_label: string,
     *     score: float,
     *     weight: float|null,
     *     contribution: float|null
     * }>
     */
    public function calculateComponents(Collection $nilaiByType, Collection $configs): array
    {
        $components = [];

        foreach ($configs as $config) {
            $records = $nilaiByType->get($config->code, collect());

            if ($records->isEmpty()) {
                continue;
            }

            $score = $this->aggregateScore($records, $config->aggregation);

            if ($score === null) {
                continue;
            }

            $weight = $config->weight;
            $contribution = $weight !== null
                ? round($score * ($weight / 100), 2)
                : null;

            $components[$config->code] = [
                'assessment_type_id' => $config->id,
                'assessment_code'    => $config->code,
                'assessment_label'   => $config->label,
                'score'              => round($score, 2),
                'weight'             => $weight,
                'contribution'       => $contribution,
            ];
        }

        return $components;
    }

    /**
     * Calculate the final raport score.
     *
     * When the registry defines weights for every scored component, the final
     * is the raw sum of score × weight/100. Otherwise we fall back to the
     * exact pre-5.3 legacy formula so existing raports keep the same nilai_akhir.
     */
    public function calculateFinal(array $components): ?float
    {
        if ($this->shouldUseRegistryMode($components)) {
            $contributions = collect($components)
                ->whereNotNull('contribution')
                ->pluck('contribution');

            if ($contributions->isEmpty()) {
                return null;
            }

            return round($contributions->sum(), 2);
        }

        return $this->calculateLegacyFinal($components);
    }

    /**
     * Determine the predicate letter from a final score.
     */
    public function determinePredikat(?float $nilaiAkhir): ?string
    {
        if ($nilaiAkhir === null) {
            return null;
        }

        return match (true) {
            $nilaiAkhir >= 85 => 'A',
            $nilaiAkhir >= 70 => 'B',
            $nilaiAkhir >= 60 => 'C',
            $nilaiAkhir >= 50 => 'D',
            default          => 'E',
        };
    }

    /**
     * Aggregate a collection of nilai records using the configured method.
     */
    private function aggregateScore(Collection $records, string $method): ?float
    {
        return match ($method) {
            'latest' => (float) $records->sortByDesc('tanggal')->first()?->nilai,
            default  => round($records->avg('nilai'), 2),
        };
    }

    /**
     * Map component scores back to the legacy raport_nilai columns.
     *
     * @param array<string, array{score: float}> $components
     * @return array<string, float|null>
     */
    public function legacyColumnMap(array $components): array
    {
        $codeToColumn = [
            'harian'  => 'nilai_harian',
            'uts'     => 'nilai_uts',
            'uas'     => 'nilai_uas',
            'praktik' => 'nilai_praktik',
        ];

        $map = [
            'nilai_harian'  => null,
            'nilai_uts'     => null,
            'nilai_uas'     => null,
            'nilai_praktik' => null,
        ];

        foreach ($components as $code => $component) {
            if (isset($codeToColumn[$code])) {
                $map[$codeToColumn[$code]] = $component['score'];
            }
        }

        return $map;
    }

    /**
     * Use registry weights only when every scored component has a registry weight.
     * Otherwise preserve the pre-5.3 formula.
     */
    private function shouldUseRegistryMode(array $components): bool
    {
        foreach ($components as $component) {
            if ($component['weight'] === null || $component['contribution'] === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Exact pre-5.3 weighted-final formula, including the praktik-absent case.
     *
     * Kept as a backward-compatibility fallback for tenants/programs whose
     * assessment configs do not yet have registry weights.
     */
    private function calculateLegacyFinal(array $components): ?float
    {
        $harian  = $components['harian']['score'] ?? 0;
        $uts     = $components['uts']['score'] ?? 0;
        $uas     = $components['uas']['score'] ?? 0;
        $praktik = $components['praktik']['score'] ?? null;

        $hasCore = ($components['harian']['score'] ?? null) !== null
            || ($components['uts']['score'] ?? null) !== null
            || ($components['uas']['score'] ?? null) !== null;

        if (! $hasCore) {
            return null;
        }

        if ($praktik !== null) {
            return round(($harian * 0.3) + ($uts * 0.2) + ($uas * 0.3) + ($praktik * 0.2), 2);
        }

        return round(($harian * 0.4) + ($uts * 0.3) + ($uas * 0.3), 2);
    }
}
