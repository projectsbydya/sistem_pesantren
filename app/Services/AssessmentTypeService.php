<?php

namespace App\Services;

use App\Models\ProgramAssessmentConfig;
use Illuminate\Support\Collection;

class AssessmentTypeService
{
    /**
     * Canonical UI color for known assessment type codes.
     * Unknown codes fall back to a deterministic palette via fallbackColor().
     */
    private const TYPE_COLORS = [
        'quiz'    => 'rose',
        'tugas'   => 'orange',
        'harian'  => 'emerald',
        'uts'     => 'blue',
        'uas'     => 'violet',
        'praktik' => 'amber',
    ];

    /**
     * Default summary aggregation for the nilai progress view.
     * This matches the current UI behaviour: harian shows an average,
     * all other types show the latest value.
     */
    private const TYPE_AGGREGATIONS = [
        'harian' => 'average',
    ];

    /**
     * Get active assessment types configured for the current tenant and program.
     *
     * @return Collection<int, object{
     *     id: int,
     *     code: string,
     *     label: string,
     *     weight: float|null,
     *     sort_order: int,
     *     is_active: bool,
     *     color: string,
     *     aggregation: string
     * }>
     */
    public function getActiveTypes(int $programId): Collection
    {
        return ProgramAssessmentConfig::with('assessmentType')
            ->where('program_id', $programId)
            ->where('is_active', true)
            ->whereHas('assessmentType', fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('assessment_type_id')
            ->get()
            ->map(fn (ProgramAssessmentConfig $config) => (object) [
                'id'          => $config->assessmentType->id,
                'code'        => $config->assessmentType->code,
                'label'       => $config->assessmentType->label,
                'weight'      => $config->weight,
                'sort_order'  => $config->sort_order,
                'is_active'   => $config->is_active,
                'color'       => $this->colorForCode($config->assessmentType->code),
                'aggregation' => $this->aggregationForCode($config->assessmentType->code),
            ]);
    }

    /**
     * Get the active assessment type codes for a program as a comma-separated string
     * (useful for validation rules).
     */
    public function getActiveCodes(int $programId): string
    {
        return $this->getActiveTypes($programId)
            ->pluck('code')
            ->implode(',');
    }

    /**
     * Get the default active assessment type code for a program.
     */
    public function getDefaultTypeCode(int $programId): ?string
    {
        return $this->getActiveTypes($programId)->first()?->code;
    }

    /**
     * Get an associative array of active assessment colors keyed by type code.
     *
     * @return array<string, string>
     */
    public function getColors(int $programId): array
    {
        return $this->getActiveTypes($programId)
            ->mapWithKeys(fn (object $type) => [$type->code => $type->color])
            ->all();
    }

    /**
     * Get an associative array of active assessment weights keyed by type code.
     *
     * @return array<string, float|null>
     */
    public function getWeights(int $programId): array
    {
        return $this->getActiveTypes($programId)
            ->mapWithKeys(fn (object $type) => [$type->code => $type->weight])
            ->all();
    }

    /**
     * Determine whether the given assessment type code is active for the
     * current tenant and program.
     */
    public function validateType(int $programId, string $typeCode): bool
    {
        return ProgramAssessmentConfig::where('program_id', $programId)
            ->where('is_active', true)
            ->whereHas(
                'assessmentType',
                fn ($query) => $query->where('is_active', true)->where('code', $typeCode)
            )
            ->exists();
    }

    private function colorForCode(string $code): string
    {
        return self::TYPE_COLORS[$code]
            ?? $this->fallbackColor($code);
    }

    private function fallbackColor(string $code): string
    {
        $palette = ['gray', 'red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose'];
        $index   = array_sum(array_map('ord', str_split($code))) % count($palette);

        return $palette[$index];
    }

    private function aggregationForCode(string $code): string
    {
        return self::TYPE_AGGREGATIONS[$code] ?? 'latest';
    }
}
