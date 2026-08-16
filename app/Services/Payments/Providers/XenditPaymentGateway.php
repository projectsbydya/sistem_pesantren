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
 * XenditPaymentGateway
 *
 * Self-configuring Xendit Invoice API provider.
 * Credentials and feature flags are read directly from config/payments.php
 * so this class can be bound in the service container and resolved anywhere
 * without manually passing a config array.
 *
 * Sandbox and production use the same Xendit API endpoint — the difference
 * lies only in the credentials (test vs. live secret key).
 *
 * Config keys consumed (all under payments.gateways.xendit):
 *   - secret_key    : Xendit secret key (Basic auth username)
 *   - webhook_token : x-callback-token header value expected on webhooks
 *
 * Normalised status vocabulary (returned by getPaymentStatus):
 *   pending | paid | expired
 */
class XenditPaymentGateway implements PaymentGatewayInterface
{
    private const GATEWAY_NAME = 'xendit';

    private const BASE_URL         = 'https://api.xendit.co';
    private const INVOICES_PATH    = '/v2/invoices';
    private const INVOICE_GET_PATH = '/v2/invoices/%s';

    private const TIMEOUT         = 30;
    private const CONNECT_TIMEOUT = 10;

    private string $secretKey;
    private string $webhookToken;

    public function __construct()
    {
        $this->secretKey    = (string) config('payments.gateways.xendit.secret_key', '');
        $this->webhookToken = (string) config('payments.gateways.xendit.webhook_token', '');
    }

    // =========================================================================
    // PaymentGatewayInterface
    // =========================================================================

    /**
     * Create a Xendit invoice and return a normalised payment-link array.
     *
     * Returned keys:
     *   - external_id  : Xendit invoice ID (e.g. "inv_xxxxxxxxxxxxxxxx")
     *   - payment_url  : URL the customer opens to pay
     *   - expires_at   : ISO-8601 expiry date from Xendit or derived from due_date
     *   - raw          : Full Xendit response body
     *
     * @throws PaymentGatewayException
     */
    public function createPaymentLink(Invoice $invoice): array
    {
        $payload = $this->buildInvoicePayload($invoice);

        $response = $this->post(self::INVOICES_PATH, $payload);
        $body     = $response->json() ?? [];

        $externalId = $body['id'] ?? null;
        $paymentUrl = $body['invoice_url'] ?? null;

        if (empty($externalId)) {
            throw new PaymentGatewayException(
                'Xendit response missing invoice id',
                self::GATEWAY_NAME,
                'MISSING_ID',
                ['response' => $body]
            );
        }

        if (empty($paymentUrl)) {
            throw new PaymentGatewayException(
                'Xendit response missing invoice_url',
                self::GATEWAY_NAME,
                'MISSING_INVOICE_URL',
                ['response' => $body]
            );
        }

        Log::info('Xendit payment link created', [
            'gateway'        => self::GATEWAY_NAME,
            'invoice_number' => $invoice->invoice_number,
            'external_id'    => $externalId,
        ]);

        return [
            'external_id' => $externalId,
            'payment_url' => $paymentUrl,
            'expires_at'  => $body['expiry_date'] ?? $invoice->due_date?->endOfDay()->toIso8601String(),
            'raw'         => $body,
        ];
    }

    /**
     * Query the current status of a Xendit invoice.
     *
     * Returned keys:
     *   - external_id : as passed in
     *   - status      : pending | paid | expired
     *   - amount      : float invoice amount
     *   - paid_at     : ISO-8601 string or null
     *   - raw         : Full Xendit response body
     *
     * @throws PaymentGatewayException
     */
    public function getPaymentStatus(string $externalId): array
    {
        $path     = sprintf(self::INVOICE_GET_PATH, rawurlencode($externalId));
        $response = $this->get($path);
        $body     = $response->json() ?? [];

        $status = $this->normaliseStatus($body['status'] ?? 'UNKNOWN');

        Log::debug('Xendit payment status checked', [
            'gateway'     => self::GATEWAY_NAME,
            'external_id' => $externalId,
            'status'      => $status,
        ]);

        return [
            'external_id' => $externalId,
            'status'      => $status,
            'amount'      => (float) ($body['amount'] ?? 0),
            'paid_at'     => $body['paid_at'] ?? null,
            'raw'         => $body,
        ];
    }

    /**
     * Verify that an inbound request genuinely originates from Xendit.
     *
     * Xendit signs webhook deliveries by setting the x-callback-token header
     * to the value you configure in their dashboard (stored as webhook_token).
     * Comparison is timing-safe (hash_equals). Never throws — returns false on
     * any failure so callers can safely gate without try/catch.
     */
    public function verifyWebhook(Request $request): bool
    {
        try {
            if (empty($this->webhookToken)) {
                Log::warning('Xendit webhook verification skipped: webhook_token not configured', [
                    'gateway' => self::GATEWAY_NAME,
                ]);
                return false;
            }

            $incoming = (string) $request->header('x-callback-token', '');

            if (empty($incoming)) {
                Log::warning('Xendit webhook rejected: missing x-callback-token header', [
                    'gateway' => self::GATEWAY_NAME,
                    'ip'      => $request->ip(),
                ]);
                return false;
            }

            $valid = hash_equals($this->webhookToken, $incoming);

            if (! $valid) {
                Log::warning('Xendit webhook rejected: token mismatch', [
                    'gateway' => self::GATEWAY_NAME,
                    'ip'      => $request->ip(),
                ]);
            }

            return $valid;
        } catch (Throwable $e) {
            Log::error('Xendit webhook verification threw an exception', [
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
     * The gateway is ready when the secret key is non-empty.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->secretKey);
    }

    // =========================================================================
    // Internal HTTP helpers
    // =========================================================================

    /**
     * POST to the Xendit API.
     *
     * @throws PaymentGatewayException
     */
    private function post(string $path, array $payload): Response
    {
        return $this->request('post', $path, $payload);
    }

    /**
     * GET from the Xendit API.
     *
     * @throws PaymentGatewayException
     */
    private function get(string $path): Response
    {
        return $this->request('get', $path, []);
    }

    /**
     * Perform an authenticated HTTP request and handle errors uniformly.
     *
     * @throws PaymentGatewayException
     */
    private function request(string $method, string $path, array $payload): Response
    {
        $url = self::BASE_URL . $path;

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
            Log::error('Xendit HTTP request failed', [
                'gateway' => self::GATEWAY_NAME,
                'url'     => $url,
                'method'  => strtoupper($method),
                'error'   => $e->getMessage(),
            ]);

            throw new PaymentGatewayException(
                'Xendit HTTP request failed: ' . $e->getMessage(),
                self::GATEWAY_NAME,
                'HTTP_ERROR',
                ['url' => $url],
                0,
                $e
            );
        }
    }

    /**
     * Build the standard HTTP headers for Xendit API requests.
     * Xendit uses HTTP Basic auth with the secret key as the username and an
     * empty password.
     *
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
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
            ?? $body['error_message']
            ?? $body['error']
            ?? 'Unknown Xendit error';

        Log::error('Xendit API error', [
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
     * Build the invoice creation payload from an Invoice model.
     *
     * @return array<string, mixed>
     */
    private function buildInvoicePayload(Invoice $invoice): array
    {
        $durationSeconds = max(86400, (int) now()->diffInSeconds($invoice->due_date->endOfDay()));

        $payload = [
            'external_id'      => $invoice->invoice_number,
            'amount'           => (int) $invoice->amount,
            'description'      => $invoice->period_label ?? $invoice->invoice_number,
            'invoice_duration' => $durationSeconds,
            'currency'         => 'IDR',
        ];

        $tenantEmail = optional($invoice->tenant)->email;
        if (! empty($tenantEmail)) {
            $payload['customer'] = ['email' => $tenantEmail];
        }

        return $payload;
    }

    /**
     * Map Xendit invoice status strings to the normalised vocabulary.
     *
     * Xendit statuses: PENDING | PAID | SETTLED | EXPIRED | UNKNOWN
     * Our vocabulary:  pending | paid | expired
     */
    private function normaliseStatus(string $xenditStatus): string
    {
        return match (strtoupper($xenditStatus)) {
            'PAID', 'SETTLED' => 'paid',
            'EXPIRED'         => 'expired',
            default           => 'pending',
        };
    }
}
