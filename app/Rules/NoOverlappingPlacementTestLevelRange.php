<?php

namespace App\Rules;

use App\Models\PlacementTestLevel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoOverlappingPlacementTestLevelRange implements ValidationRule
{
    public function __construct(
        private int $programId,
        private ?int $excludeId = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // This rule is intended to be used on the max_score field after min_score is present.
        $data = request()->all();
        $minScore = $data['min_score'] ?? null;
        $maxScore = $value ?? null;

        if (! is_numeric($minScore) || ! is_numeric($maxScore)) {
            return;
        }

        $minScore = (int) $minScore;
        $maxScore = (int) $maxScore;

        if ($minScore > $maxScore) {
            return;
        }

        $query = PlacementTestLevel::active()
            ->where('tenant_id', tenant_id())
            ->where('program_id', $this->programId)
            ->whereRaw('? <= max_score', [$minScore])
            ->whereRaw('min_score <= ?', [$maxScore]);

        if ($this->excludeId !== null) {
            $query->where('id', '!=', $this->excludeId);
        }

        if ($query->exists()) {
            $fail('Rentang skor level ini bertabrakan dengan level lain di program yang sama.');
        }
    }
}
