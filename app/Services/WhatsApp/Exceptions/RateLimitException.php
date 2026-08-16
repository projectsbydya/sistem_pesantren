<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Exceptions;

/**
 * Exception thrown when rate limit is exceeded.
 */
class RateLimitException extends WhatsAppException
{
    private int $retryAfter;

    public function __construct(
        string $message = 'Rate limit exceeded',
        ?string $provider = null,
        int $retryAfter = 60,
        array $context = [],
        int $code = 429,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $provider, 'RATE_LIMIT', $context, $code, $previous);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
