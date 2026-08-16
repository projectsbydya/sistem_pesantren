<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Abstract base class for payment gateway providers.
 *
 * Implements common HTTP transport, logging, and configuration
 * checking so concrete providers stay focused on gateway-specific logic.
 */
abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected string $gatewayName;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }

    /**
     * {@inheritDoc}
     */
    public function isConfigured(): bool
    {
        return collect($this->getRequiredConfigKeys())
            ->every(fn (string $key) => ! empty($this->config[$key] ?? null));
    }

    /**
     * Return the list of config array keys that must be non-empty for the
     * gateway to be considered "configured".
     *
     * @return array<string>
     */
    abstract protected function getRequiredConfigKeys(): array;

    /**
     * Make an authenticated HTTP request to the gateway API.
     *
     * @throws PaymentGatewayException
     */
    protected function makeRequest(string $method, string $url, array $payload = []): Response
    {
        try {
            $response = Http::withOptions([
                'timeout'         => 30,
                'connect_timeout' => 10,
            ])
                ->withHeaders($this->getHeaders())
                ->$method($url, $payload);

            if ($response->clientError() || $response->serverError()) {
                $this->handleErrorResponse($response);
            }

            return $response;
        } catch (PaymentGatewayException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new PaymentGatewayException(
                'HTTP request failed: ' . $e->getMessage(),
                $this->gatewayName,
                'HTTP_ERROR',
                ['url' => $url],
                0,
                $e
            );
        }
    }

    /**
     * Build HTTP headers for API calls. Override per gateway as needed.
     *
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Throw a normalised exception from a failed HTTP response.
     *
     * @throws PaymentGatewayException
     */
    protected function handleErrorResponse(Response $response, string $context = ''): never
    {
        $body    = $response->json() ?? [];
        $message = $body['message']
            ?? $body['error_message']
            ?? $body['error']
            ?? 'Unknown gateway error';

        $prefix = $context ? "{$context}: " : '';

        throw new PaymentGatewayException(
            "{$prefix}{$message}",
            $this->gatewayName,
            (string) $response->status(),
            ['response' => $body, 'status' => $response->status()]
        );
    }

    /**
     * Log a successful payment link creation.
     */
    protected function logPaymentCreated(string $invoiceNumber, string $externalId): void
    {
        Log::info('Payment link created', [
            'gateway'        => $this->gatewayName,
            'invoice_number' => $invoiceNumber,
            'external_id'    => $externalId,
        ]);
    }

    /**
     * Log a status query.
     */
    protected function logStatusCheck(string $externalId, string $status): void
    {
        Log::debug('Payment status checked', [
            'gateway'     => $this->gatewayName,
            'external_id' => $externalId,
            'status'      => $status,
        ]);
    }
}
