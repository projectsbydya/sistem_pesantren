<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSetupProgress extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'tenant_setup_progress';

    protected $fillable = [
        'tenant_id',
        'step_program_selected',
        'step_program_selected_at',
        'step_branches_setup',
        'step_branches_setup_at',
        'step_kelas_template_applied',
        'step_kelas_template_applied_at',
        'step_subjects_template_applied',
        'step_subjects_template_applied_at',
        'step_first_santri_added',
        'step_first_santri_added_at',
        'step_first_ustadz_added',
        'step_first_ustadz_added_at',
        'step_jadwal_setup',
        'step_jadwal_setup_at',
        'setup_status',
        'completed_at',
        'progress_percentage',
    ];

    protected $casts = [
        'step_program_selected' => 'boolean',
        'step_program_selected_at' => 'datetime',
        'step_branches_setup' => 'boolean',
        'step_branches_setup_at' => 'datetime',
        'step_kelas_template_applied' => 'boolean',
        'step_kelas_template_applied_at' => 'datetime',
        'step_subjects_template_applied' => 'boolean',
        'step_subjects_template_applied_at' => 'datetime',
        'step_first_santri_added' => 'boolean',
        'step_first_santri_added_at' => 'datetime',
        'step_first_ustadz_added' => 'boolean',
        'step_first_ustadz_added_at' => 'datetime',
        'step_jadwal_setup' => 'boolean',
        'step_jadwal_setup_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Mark a step as completed
     */
    public function completeStep(string $step): void
    {
        $field = "step_{$step}";
        $timestampField = "step_{$step}_at";

        if (array_key_exists($field, $this->getAttributes())) {
            $this->update([
                $field => true,
                $timestampField => now(),
            ]);
            $this->recalculateProgress();
        }
    }

    /**
     * Check if a step is completed
     */
    public function isStepComplete(string $step): bool
    {
        $field = "step_{$step}";
        return $this->getAttribute($field) ?? false;
    }

    /**
     * Mark a step as incomplete (for destroy/downgrade scenarios)
     */
    public function uncompleteStep(string $step): void
    {
        $field = "step_{$step}";
        $timestampField = "step_{$step}_at";

        if (array_key_exists($field, $this->getAttributes())) {
            $this->update([
                $field => false,
                $timestampField => null,
            ]);
            $this->recalculateProgress();
        }
    }

    /**
     * Recalculate progress percentage and status from LIVE tenant data.
     *
     * The required step chain and its order come from the onboarding
     * registry (config/onboarding.php via OnboardingStepRegistry), not from
     * a hardcoded list here. This means adding/reordering wizard steps never
     * requires a DB migration — completion of every step (including ones
     * with no dedicated `step_*` column, e.g. teaching assignment) is
     * derived from \App\Services\TenantSetupService::getActualProgress().
     *
     * The individual `step_*` boolean columns are kept only as legacy
     * "first reached at" markers for analytics; they are not authoritative
     * for gating.
     */
    public function recalculateProgress(): void
    {
        $actual = \App\Services\TenantSetupService::getActualProgress($this->tenant_id);
        $programSlug = \App\Services\TenantSetupService::getFirstActiveProgram($this->tenant_id)?->slug;

        $requiredComplete = $actual['hasProgram']
            && \App\Services\OnboardingStepRegistry::isFlowComplete($actual, $programSlug);

        $status = match (true) {
            $requiredComplete => 'siap_operasional',
            $actual['completed'] > 0 => 'in_progress',
            default => 'pending',
        };

        $this->update([
            'progress_percentage' => $actual['percentage'],
            'setup_status' => $status,
            'completed_at' => $requiredComplete ? ($this->completed_at ?? now()) : null,
        ]);
    }

    /**
     * Get next incomplete step with guidance.
     *
     * Order and metadata come from OnboardingStepRegistry (config/onboarding.php),
     * resolved for the tenant's active program — NOT hardcoded here. Completion
     * is derived from live tenant data via TenantSetupService::getActualProgress().
     */
    public function getNextStep(): ?array
    {
        $actual = \App\Services\TenantSetupService::getActualProgress($this->tenant_id);

        if (!$actual['hasProgram']) {
            return $this->attachRouteParams([
                'key' => 'program_selected',
                'title' => 'Pilih Program',
                'description' => 'Pilih program-program yang akan digunakan pesantren Anda',
                'route' => 'dashboard.onboarding.programs',
                'icon' => 'fa-layer-group',
            ]);
        }

        $programSlug = \App\Services\TenantSetupService::getFirstActiveProgram($this->tenant_id)?->slug;
        $nextKey = \App\Services\OnboardingStepRegistry::firstIncompleteKey($actual, $programSlug);

        if (!$nextKey) {
            return null;
        }

        $def = \App\Services\OnboardingStepRegistry::find($nextKey, $programSlug);

        return $this->attachRouteParams([
            'key' => $nextKey,
            'title' => $def['title'] ?? $def['label'],
            'description' => $def['description'] ?? '',
            'route' => 'dashboard.onboarding.wizard',
            'icon' => $def['icon'] ?? 'fa-arrow-right',
            'wizard_step' => $nextKey,
        ]);
    }

    /**
     * Attach required route parameters for the next-step link.
     *
     * - Program-scoped akademik routes (URI: /dashboard/akademik/{programSlug}/...)
     *   require a programSlug. Resolve it dynamically from the tenant's first active
     *   program (no hardcoded slugs, multi-tenant safe).
     * - The onboarding wizard route requires a `step` query param.
     * - Other routes get no extra params.
     */
    protected function attachRouteParams(array $step): array
    {
        $step['params'] = [];

        if (str_starts_with($step['route'], 'dashboard.akademik.')) {
            $program = \App\Services\TenantSetupService::getFirstActiveProgram($this->tenant_id);

            if ($program) {
                $step['params'] = ['programSlug' => $program->slug];
            }
        } elseif ($step['route'] === 'dashboard.onboarding.wizard' && isset($step['wizard_step'])) {
            $step['params'] = ['step' => $step['wizard_step']];
        }

        return $step;
    }

    /**
     * Get all steps with status, in registry order, for the tenant's active program.
     * required=true means the step must be complete for siap_operasional.
     * 'done' reflects LIVE tenant data (see TenantSetupService::getActualProgress()).
     */
    public function getAllSteps(): array
    {
        $actual = \App\Services\TenantSetupService::getActualProgress($this->tenant_id);
        $programSlug = \App\Services\TenantSetupService::getFirstActiveProgram($this->tenant_id)?->slug;

        $steps = [[
            'key' => 'program_selected',
            'label' => 'Pilih Program',
            'icon' => 'fa-layer-group',
            'required' => true,
            'done' => $actual['hasProgram'],
        ]];

        foreach (\App\Services\OnboardingStepRegistry::flow($programSlug) as $def) {
            $steps[] = [
                'key' => $def['key'],
                'label' => $def['label'],
                'icon' => $def['icon'] ?? 'fa-circle',
                'required' => $def['required'] ?? false,
                'done' => \App\Services\OnboardingStepRegistry::isStepComplete($def['key'], $actual, $programSlug),
            ];
        }

        return $steps;
    }

    public function scopeIncomplete($query)
    {
        return $query->where('setup_status', '!=', 'siap_operasional');
    }

    public function scopeSiapOperasional($query)
    {
        return $query->where('setup_status', 'siap_operasional');
    }
}
