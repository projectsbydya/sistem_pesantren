<?php

declare(strict_types=1);

namespace App\Services\Payments\Exceptions;

use Exception;

/**
 * Base exception for payment gateway errors.
 */
class PaymentGatewayException extends Exception
{
    private ?string $gateway;
    private ?string $errorCode;
    private array $context;

    public function __construct(
        string $message = '',
        ?string $gateway = null,
        ?string $errorCode = null,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->gateway   = $gateway;
        $this->errorCode = $errorCode;
        $this->context   = $context;
    }

    public function getGateway(): ?string
    {
        return $this->gateway;
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
            'message'    => $this->getMessage(),
            'gateway'    => $this->gateway,
            'error_code' => $this->errorCode,
            'context'    => $this->context,
        ];
    }
}
