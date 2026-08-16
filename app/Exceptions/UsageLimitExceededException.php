<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class UsageLimitExceededException extends Exception
{
    public function __construct(
        public readonly string $metric,
        public readonly int|float $limit,
        public readonly int|float $currentUsage,
        public readonly int|float $attemptedIncrement,
    ) {
        $limitLabel = $limit === 0 ? 'unlimited' : $limit;
        $message = sprintf(
            '%s limit exceeded. Plan allows %s, current usage is %s, attempted to add %s.',
            $this->formatMetricName($metric),
            $limitLabel,
            $currentUsage,
            $attemptedIncrement
        );

        parent::__construct($message, 422);
    }

    private function formatMetricName(string $metric): string
    {
        $label = config("usage.metrics.{$metric}.label");

        if ($label) {
            return $label;
        }

        return match ($metric) {
            'user_count' => 'User',
            'santri_count' => 'Santri',
            'branch_count' => 'Branch',
            'storage_usage_mb' => 'Storage',
            default => ucfirst(str_replace('_', ' ', $metric)),
        };
    }

    public function getMetric(): string
    {
        return $this->metric;
    }

    public function getLimit(): int|float
    {
        return $this->limit;
    }

    public function getCurrentUsage(): int|float
    {
        return $this->currentUsage;
    }

    public function getAttemptedIncrement(): int|float
    {
        return $this->attemptedIncrement;
    }
}
