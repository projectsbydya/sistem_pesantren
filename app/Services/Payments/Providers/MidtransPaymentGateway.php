<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Models\Invoice;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * MidtransPaymentGateway
 *
 * Self-configuring Midtrans Snap API provider.
 * Credentials and environment flags are read directly from config/payments.php
 * so this class can be bound in the service container and resolved anywhere
 * without manually passing a config array.
 *
 * Sandbox vs. production use different base URLs — controlled by is_production.
 * The credentials (server_key / client_key) differ between environments but the
 * code path is identical.
 *
 * Config keys consumed (all under payments.gateways.midtrans):
 *   - server_key    : Midtrans Server Key (Basic auth username)
 *   - client_key    : Midtrans Client Key (used for frontend SDK, stored for completeness)
 *   - is_production : bool — true = production endpoints, false = sandbox
 *
 * Normalised status vocabulary (returned by getPaymentStatus):
 *   pending | paid | failed | expired
 */
class MidtransPaymentGateway implements PaymentGatewayInterface
{
    private const GATEWAY_NAME = 'midtrans';

    private const SNAP_URL_SANDBOX    = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    private const SNAP_URL_PRODUCTION = 'https://app.midtrans.com/snap/v1/transactions';

    private const STATUS_URL_SANDBOX    = 'https://api.sandbox.midtrans.com/v2/%s/status';
    private const STATUS_URL_PRODUCTION = 'https://api.midtrans.com/v2/%s/status';

    private const TIMEOUT         = 30;
    private const CONNECT_TIMEOUT = 10;

    private string $serverKey;
    private string $clientKey;
    private bool   $isProduction;

    public function __construct()
    {
        $this->serverKey    = (string) config('payments.gateways.midtrans.server_key', '');
        $this->clientKey    = (string) config('payments.gateways.midtrans.client_key', '');
        $this->isProduction = (bool)   config('payments.gateways.midtrans.is_production', false);
    }

    // =========================================================================
    // PaymentGatewayInterface
    // =========================================================================

    /**
     * Create a Midtrans Snap transaction and return a normalised payment-link array.
     *
     * Returned keys:
     *   - external_id  : Midtrans Snap token (used as transaction identifier)
     *   - payment_url  : Snap redirect URL the customer opens to pay
     *   - expires_at   : ISO-8601 derived from invoice due_date (Midtrans does not
     *                    return an expiry timestamp in the Snap response)
     *   - raw          : Full Midtrans Snap response body
     *
     * @throws PaymentGatewayException
     */
    public function createPaymentLink(Invoice $invoice): array
    {
        $payload = $this->buildSnapPayload($invoice);

        $response = $this->post($this->snapUrl(), $payload);
        $body     = $response->json() ?? [];

        $externalId = $body['token'] ?? null;
        $paymentUrl = $body['redirect_url'] ?? null;

        if (empty($externalId)) {
            throw new PaymentGatewayException(
                'Midtrans response missing token',
                self::GATEWAY_NAME,
                'MISSING_TOKEN',
                ['response' => $body]
            );
        }

        if (empty($paymentUrl)) {
            throw new PaymentGatewayException(
                'Midtrans response missing redirect_url',
                self::GATEWAY_NAME,
                'MISSING_REDIRECT_URL',
                ['response' => $body]
            );
        }

        Log::info('Midtrans payment link created', [
            'gateway'        => self::GATEWAY_NAME,
            'invoice_number' => $invoice->invoice_number,
            'external_id'    => $externalId,
        ]);

        return [
            'external_id' => $externalId,
            'payment_url' => $paymentUrl,
            'expires_at'  => $invoice->due_date?->endOfDay()->toIso8601String(),
            'raw'         => $body,
        ];
    }

    /**
     * Query the current status of a Midtrans transaction.
     *
     * Returned keys:
     *   - external_id : as passed in (order_id in Midtrans vocabulary)
     *   - status      : pending | paid | failed | expired
     *   - amount      : float gross_amount
     *   - paid_at     : settlement_time string or null
     *   - raw         : Full Midtrans status response body
     *
     * @throws PaymentGatewayException
     */
    public function getPaymentStatus(string $externalId): array
    {
        $url      = sprintf($this->statusUrl(), rawurlencode($externalId));
        $response = $this->get($url);
        $body     = $response->json() ?? [];

        $status = $this->normaliseStatus($body['transaction_status'] ?? 'unknown');

        Log::debug('Midtrans payment status checked', [
            'gateway'     => self::GATEWAY_NAME,
            'external_id' => $externalId,
            'status'      => $status,
        ]);

        return [
            'external_id' => $externalId,
            'status'      => $status,
            'amount'      => (float) ($body['gross_amount'] ?? 0),
            'paid_at'     => $body['settlement_time'] ?? null,
            'raw'         => $body,
        ];
    }

    /**
     * Verify that an inbound webhook request genuinely originates from Midtrans.
     *
     * Midtrans signs each notification with:
     *   SHA-512( order_id + status_code + gross_amount + server_key )
     *
     * and sends the result as the `signature_key` POST field.
     * Comparison is timing-safe (hash_equals). Never throws — returns false on
     * any failure so callers can safely gate without try/catch.
     */
    public function verifyWebhook(Request $request): bool
    {
        try {
            if (empty($this->serverKey)) {
                Log::warning('Midtrans webhook verification skipped: server_key not configured', [
                    'gateway' => self::GATEWAY_NAME,
                ]);
                return false;
            }

            $incoming = (string) $request->input('signature_key', '');

            if (empty($incoming)) {
                Log::warning('Midtrans webhook rejected: missing signature_key field', [
                    'gateway' => self::GATEWAY_NAME,
                    'ip'      => $request->ip(),
                ]);
                return false;
            }

            $orderId     = (string) $request->input('order_id', '');
            $statusCode  = (string) $request->input('status_code', '');
            $grossAmount = (string) $request->input('gross_amount', '');

            $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
            $valid    = hash_equals($expected, $incoming);

            if (! $valid) {
                Log::warning('Midtrans webhook rejected: signature mismatch', [
                    'gateway'  => self::GATEWAY_NAME,
                    'order_id' => $orderId,
                    'ip'       => $request->ip(),
                ]);
            }

            return $valid;
        } catch (Throwable $e) {
            Log::error('Midtrans webhook verification threw an exception', [
                'gateway' => self::GATEWAY_NAME,
                'error'   => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayName(): string
    {
        return self::GATEWAY_NAME;
    }

    /**
     * The gateway is ready when both server_key and client_key are non-empty.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->serverKey) && ! empty($this->clientKey);
    }

    // =========================================================================
    // Internal HTTP helpers
    // =========================================================================

    /**
     * POST to a Midtrans API endpoint.
     *
     * @throws PaymentGatewayException
     */
    private function post(string $url, array $payload): Response
    {
        return $this->request('post', $url, $payload);
    }

    /**
     * GET from a Midtrans API endpoint.
     *
     * @throws PaymentGatewayException
     */
    private function get(string $url): Response
    {
        return $this->request('get', $url, []);
    }

    /**
     * Perform an authenticated HTTP request and handle errors uniformly.
     *
     * @throws PaymentGatewayException
     */
    private function request(string $method, string $url, array $payload): Response
    {
        try {
            $response = Http::withOptions([
                'timeout'         => self::TIMEOUT,
                'connect_timeout' => self::CONNECT_TIMEOUT,
            ])
                ->withHeaders($this->headers())
                ->$method($url, $payload);

            if ($response->clientError() || $response->serverError()) {
                $this->throwFromResponse($response);
            }

            return $response;
        } catch (PaymentGatewayException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Midtrans HTTP request failed', [
                'gateway' => self::GATEWAY_NAME,
                'url'     => $url,
                'method'  => strtoupper($method),
                'error'   => $e->getMessage(),
            ]);

            throw new PaymentGatewayException(
                'Midtrans HTTP request failed: ' . $e->getMessage(),
                self::GATEWAY_NAME,
                'HTTP_ERROR',
                ['url' => $url],
                0,
                $e
            );
        }
    }

    /**
     * Build the standard HTTP headers for Midtrans API requests.
     * Midtrans uses HTTP Basic auth with the server_key as the username and an
     * empty password.
     *
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Throw a PaymentGatewayException from a non-2xx HTTP response.
     *
     * @throws PaymentGatewayException
     */
    private function throwFromResponse(Response $response): never
    {
        $body    = $response->json() ?? [];
        $message = $body['message']
            ?? $body['error_messages'][0]
            ?? $body['status_message']
            ?? $body['error']
            ?? 'Unknown Midtrans error';

        Log::error('Midtrans API error', [
            'gateway' => self::GATEWAY_NAME,
            'status'  => $response->status(),
            'body'    => $body,
        ]);

        throw new PaymentGatewayException(
            $message,
            self::GATEWAY_NAME,
            (string) $response->status(),
            ['response' => $body, 'http_status' => $response->status()]
        );
    }

    /**
     * Build the Snap transaction payload from an Invoice model.
     *
     * @return array<string, mixed>
     */
    private function buildSnapPayload(Invoice $invoice): array
    {
        $daysUntilDue = max(1, (int) now()->diffInDays($invoice->due_date) + 1);

        $payload = [
            'transaction_details' => [
                'order_id'     => $invoice->invoice_number,
                'gross_amount' => (int) $invoice->amount,
            ],
            'expiry' => [
                'unit'     => 'days',
                'duration' => $daysUntilDue,
            ],
        ];

        $tenantEmail = optional($invoice->tenant)->email;
        if (! empty($tenantEmail)) {
            $payload['customer_details'] = ['email' => $tenantEmail];
        }

        return $payload;
    }

    /**
     * Resolve the Snap API URL based on environment.
     */
    private function snapUrl(): string
    {
        return $this->isProduction
            ? self::SNAP_URL_PRODUCTION
            : self::SNAP_URL_SANDBOX;
    }

    /**
     * Resolve the transaction status API URL based on environment.
     */
    private function statusUrl(): string
    {
        return $this->isProduction
            ? self::STATUS_URL_PRODUCTION
            : self::STATUS_URL_SANDBOX;
    }

    /**
     * Map Midtrans transaction_status strings to the normalised vocabulary.
     *
     * Midtrans statuses: pending | capture | settlement | deny | cancel | expire | failure | refund
     * Our vocabulary:    pending | paid | failed | expired
     */
    private function normaliseStatus(string $midtransStatus): string
    {
        return match ($midtransStatus) {
            'capture', 'settlement'        => 'paid',
            'deny', 'cancel', 'failure'    => 'failed',
            'expire'                       => 'expired',
            default                        => 'pending',
        };
    }
}
