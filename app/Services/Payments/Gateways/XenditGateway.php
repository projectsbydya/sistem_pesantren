<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Models\Invoice;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Xendit payment gateway provider.
 *
 * Integrates with the Xendit Invoice API to generate payment links
 * and query invoice status.
 *
 * Required config keys:
 *   - secret_key : Xendit Secret Key (used as HTTP Basic auth username)
 */
class XenditGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'xendit';

    private const INVOICE_URL       = 'https://api.xendit.co/v2/invoices';
    private const INVOICE_GET_URL   = 'https://api.xendit.co/v2/invoices/%s';

    /**
     * {@inheritDoc}
     */
    public function createPaymentLink(Invoice $invoice): array
    {
        $payload = [
            'external_id'       => $invoice->invoice_number,
            'amount'            => (int) $invoice->amount,
            'description'       => $invoice->period_label ?? $invoice->invoice_number,
            'invoice_duration'  => max(86400, now()->diffInSeconds($invoice->due_date->endOfDay())),
            'customer'          => [
                'email' => optional($invoice->tenant)->email,
            ],
            'currency'          => 'IDR',
        ];

        $response = $this->makeRequest('post', self::INVOICE_URL, $payload);

        $body = $response->json();

        $externalId = $body['id'] ?? throw new PaymentGatewayException(
            'Xendit response missing invoice id',
            $this->gatewayName,
            'MISSING_ID',
            ['response' => $body]
        );

        $paymentUrl = $body['invoice_url'] ?? throw new PaymentGatewayException(
            'Xendit response missing invoice_url',
            $this->gatewayName,
            'MISSING_INVOICE_URL',
            ['response' => $body]
        );

        $this->logPaymentCreated($invoice->invoice_number, $externalId);

        return [
            'external_id' => $externalId,
            'payment_url' => $paymentUrl,
            'expires_at'  => $body['expiry_date'] ?? $invoice->due_date?->endOfDay()->toIso8601String(),
            'raw'         => $body,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentStatus(string $externalId): array
    {
        $url      = sprintf(self::INVOICE_GET_URL, urlencode($externalId));
        $response = $this->makeRequest('get', $url);

        $body   = $response->json();
        $status = $this->normaliseStatus($body['status'] ?? 'UNKNOWN');

        $this->logStatusCheck($externalId, $status);

        return [
            'external_id' => $externalId,
            'status'      => $status,
            'amount'      => (float) ($body['amount'] ?? 0),
            'paid_at'     => $body['paid_at'] ?? null,
            'raw'         => $body,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * Validates the x-callback-token header against config('payments.gateways.xendit.webhook_token').
     */
    public function verifyWebhook(Request $request): bool
    {
        try {
            $token    = $this->config['webhook_token'] ?? '';
            $incoming = $request->header('x-callback-token', '');

            if (empty($token) || empty($incoming)) {
                return false;
            }

            return hash_equals($token, $incoming);
        } catch (\Throwable $e) {
            Log::warning('Xendit webhook verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getHeaders(): array
    {
        $encoded = base64_encode(($this->config['secret_key'] ?? '') . ':');

        return array_merge(parent::getHeaders(), [
            'Authorization' => 'Basic ' . $encoded,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['secret_key'];
    }

    /**
     * Map Xendit invoice status to our normalised status vocabulary.
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
