<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Exceptions;

/**
 * Exception thrown when phone number is invalid.
 */
class InvalidNumberException extends WhatsAppException
{
    private string $number;

    public function __construct(
        string $number,
        ?string $provider = null,
        array $context = [],
        int $code = 400,
        ?\Exception $previous = null
    ) {
        parent::__construct(
            "Invalid phone number: {$number}",
            $provider,
            'INVALID_NUMBER',
            $context,
            $code,
            $previous
        );
        $this->number = $number;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
