<?php

namespace App\Services;

/**
 * OnboardingStepRegistry
 *
 * Resolves onboarding wizard step ORDER and metadata from config/onboarding.php.
 * This is the only place that understands "what comes next/previous" and
 * "is this step reachable yet" — controllers/models/views must go through
 * this class instead of hardcoding step keys or numbers.
 *
 * Completion is always checked against a live `$actualProgress` array (see
 * TenantSetupService::getActualProgress()), never against stored per-step
 * flags. This means adding/reordering steps never requires a DB migration.
 */
class OnboardingStepRegistry
{
    /**
     * Ordered list of step definitions for a program (or the default flow).
     */
    public static function flow(?string $programSlug = null): array
    {
        $config = config('onboarding');
        $flows = $config['flows'] ?? [];
        $flowKeys = $flows[$programSlug] ?? $flows['default'] ?? [];
        $steps = $config['steps'] ?? [];

        return collect($flowKeys)
            ->filter(fn (string $key) => isset($steps[$key]))
            ->map(fn (string $key) => array_merge(['key' => $key], $steps[$key]))
            ->values()
            ->all();
    }

    public static function keys(?string $programSlug = null): array
    {
        return array_column(self::flow($programSlug), 'key');
    }

    public static function find(string $key, ?string $programSlug = null): ?array
    {
        foreach (self::flow($programSlug) as $step) {
            if ($step['key'] === $key) {
                return $step;
            }
        }

        return null;
    }

    public static function requiredSteps(?string $programSlug = null): array
    {
        return array_values(array_filter(
            self::flow($programSlug),
            fn (array $step) => $step['required'] ?? false
        ));
    }

    public static function nextKey(string $currentKey, ?string $programSlug = null): ?string
    {
        $keys = self::keys($programSlug);
        $i = array_search($currentKey, $keys, true);

        return ($i === false || !isset($keys[$i + 1])) ? null : $keys[$i + 1];
    }

    public static function previousKey(string $currentKey, ?string $programSlug = null): ?string
    {
        $keys = self::keys($programSlug);
        $i = array_search($currentKey, $keys, true);

        return ($i === false || $i === 0) ? null : $keys[$i - 1];
    }

    /**
     * Whether $key is complete, based on live tenant data in $actualProgress
     * (see TenantSetupService::getActualProgress()).
     */
    public static function isStepComplete(string $key, array $actualProgress, ?string $programSlug = null): bool
    {
        $def = self::find($key, $programSlug);

        if (!$def) {
            return false;
        }

        $field = $def['progress_field'] ?? null;

        if ($field === null) {
            // Steps without a tracked field (e.g. the summary step) are
            // considered done once every required step in the flow is done.
            return (bool) ($actualProgress['is_complete'] ?? false);
        }

        return (bool) ($actualProgress[$field] ?? false);
    }

    /**
     * A step is only unlocked once every earlier REQUIRED step in the flow
     * is complete. This is what prevents users from ever reaching e.g.
     * Teaching Assignment before a Teacher exists, or Schedule before a
     * Teaching Assignment exists.
     */
    public static function isUnlocked(string $key, array $actualProgress, ?string $programSlug = null): bool
    {
        foreach (self::flow($programSlug) as $step) {
            if ($step['key'] === $key) {
                return true;
            }

            if (($step['required'] ?? false) && !self::isStepComplete($step['key'], $actualProgress, $programSlug)) {
                return false;
            }
        }

        return false;
    }

    /**
     * First required step that is not yet complete, or null if the whole
     * flow is complete.
     */
    public static function firstIncompleteKey(array $actualProgress, ?string $programSlug = null): ?string
    {
        foreach (self::flow($programSlug) as $step) {
            if (($step['required'] ?? false) && !self::isStepComplete($step['key'], $actualProgress, $programSlug)) {
                return $step['key'];
            }
        }

        return null;
    }

    public static function isFlowComplete(array $actualProgress, ?string $programSlug = null): bool
    {
        return self::firstIncompleteKey($actualProgress, $programSlug) === null;
    }

    /**
     * Step key a user should land on: the first incomplete required step, or
     * the LAST step of the flow (e.g. the summary) if everything is already
     * done, falling back to the first step if the flow is somehow empty.
     */
    public static function landingKey(array $actualProgress, ?string $programSlug = null): string
    {
        $keys = self::keys($programSlug);
        $incomplete = self::firstIncompleteKey($actualProgress, $programSlug);

        if ($incomplete) {
            return $incomplete;
        }

        return $keys[count($keys) - 1] ?? ($keys[0] ?? 'kelas');
    }
}
