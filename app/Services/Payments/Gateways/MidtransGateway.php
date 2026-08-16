<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Models\Invoice;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Midtrans payment gateway provider.
 *
 * Integrates with the Midtrans Snap API to generate payment links
 * and query transaction status.
 *
 * Required config keys:
 *   - server_key   : Midtrans Server Key
 *   - client_key   : Midtrans Client Key
 *   - is_production: bool (true = production, false = sandbox)
 */
class MidtransGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'midtrans';

    private const SNAP_URL_SANDBOX    = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    private const SNAP_URL_PRODUCTION = 'https://app.midtrans.com/snap/v1/transactions';

    private const STATUS_URL_SANDBOX    = 'https://api.sandbox.midtrans.com/v2/%s/status';
    private const STATUS_URL_PRODUCTION = 'https://api.midtrans.com/v2/%s/status';

    /**
     * {@inheritDoc}
     */
    public function createPaymentLink(Invoice $invoice): array
    {
        $payload = [
            'transaction_details' => [
                'order_id'     => $invoice->invoice_number,
                'gross_amount' => (int) $invoice->amount,
            ],
            'expiry' => [
                'unit'     => 'days',
                'duration' => max(1, now()->diffInDays($invoice->due_date) + 1),
            ],
            'customer_details' => [
                'email' => optional($invoice->tenant)->email,
            ],
        ];

        $response = $this->makeRequest('post', $this->snapUrl(), $payload);

        $body = $response->json();

        $externalId = $body['token'] ?? throw new PaymentGatewayException(
            'Midtrans response missing token',
            $this->gatewayName,
            'MISSING_TOKEN',
            ['response' => $body]
        );

        $paymentUrl = $body['redirect_url'] ?? throw new PaymentGatewayException(
            'Midtrans response missing redirect_url',
            $this->gatewayName,
            'MISSING_REDIRECT_URL',
            ['response' => $body]
        );

        $this->logPaymentCreated($invoice->invoice_number, $externalId);

        return [
            'external_id' => $externalId,
            'payment_url' => $paymentUrl,
            'expires_at'  => $invoice->due_date?->endOfDay()->toIso8601String(),
            'raw'         => $body,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentStatus(string $externalId): array
    {
        $url      = sprintf($this->statusUrl(), urlencode($externalId));
        $response = $this->makeRequest('get', $url);

        $body   = $response->json();
        $status = $this->normaliseStatus($body['transaction_status'] ?? 'unknown');

        $this->logStatusCheck($externalId, $status);

        return [
            'external_id' => $externalId,
            'status'      => $status,
            'amount'      => (float) ($body['gross_amount'] ?? 0),
            'paid_at'     => $body['settlement_time'] ?? null,
            'raw'         => $body,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * Validates using SHA-512 of order_id + status_code + gross_amount + server_key.
     */
    public function verifyWebhook(Request $request): bool
    {
        try {
            $orderId     = $request->input('order_id', '');
            $statusCode  = $request->input('status_code', '');
            $grossAmount = $request->input('gross_amount', '');
            $serverKey   = $this->config['server_key'] ?? '';
            $incoming    = $request->input('signature_key', '');

            if (empty($incoming) || empty($serverKey)) {
                return false;
            }

            $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            return hash_equals($expected, $incoming);
        } catch (\Throwable $e) {
            Log::warning('Midtrans webhook verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getHeaders(): array
    {
        $encoded = base64_encode(($this->config['server_key'] ?? '') . ':');

        return array_merge(parent::getHeaders(), [
            'Authorization' => 'Basic ' . $encoded,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['server_key', 'client_key'];
    }

    private function snapUrl(): string
    {
        return ($this->config['is_production'] ?? false)
            ? self::SNAP_URL_PRODUCTION
            : self::SNAP_URL_SANDBOX;
    }

    private function statusUrl(): string
    {
        return ($this->config['is_production'] ?? false)
            ? self::STATUS_URL_PRODUCTION
            : self::STATUS_URL_SANDBOX;
    }

    /**
     * Map Midtrans transaction_status to our normalised status vocabulary.
     */
    private function normaliseStatus(string $midtransStatus): string
    {
        return match ($midtransStatus) {
            'capture', 'settlement' => 'paid',
            'deny', 'cancel', 'failure' => 'failed',
            'expire'                => 'expired',
            default                 => 'pending',
        };
    }
}
