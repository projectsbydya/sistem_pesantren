<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Exceptions;

use Exception;

/**
 * Base exception for WhatsApp service errors.
 */
class WhatsAppException extends Exception
{
    private ?string $provider;
    private ?string $errorCode;
    private array $context;

    public function __construct(
        string $message = '',
        ?string $provider = null,
        ?string $errorCode = null,
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->provider = $provider;
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'provider' => $this->provider,
            'error_code' => $this->errorCode,
            'context' => $this->context,
        ];
    }
}
