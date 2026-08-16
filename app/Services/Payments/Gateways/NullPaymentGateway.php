<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Models\Invoice;
use Illuminate\Http\Request;

/**
 * Null (offline / manual) payment gateway.
 *
 * Used when no external payment gateway is configured.
 * createPaymentLink() returns a fake internal URL so the rest of the
 * application can treat all payment flows uniformly.
 * verifyWebhook() always returns false — manual payments are confirmed
 * by an admin through the back-office, not via webhook.
 */
class NullPaymentGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'null';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentLink(Invoice $invoice): array
    {
        $externalId = 'MANUAL-' . $invoice->invoice_number;

        return [
            'external_id' => $externalId,
            'payment_url' => route('dashboard.super-admin.invoices.show', $invoice, true),
            'expires_at'  => $invoice->due_date?->endOfDay()->toIso8601String(),
            'raw'         => [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentStatus(string $externalId): array
    {
        return [
            'external_id' => $externalId,
            'status'      => 'pending',
            'amount'      => 0,
            'paid_at'     => null,
            'raw'         => [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function verifyWebhook(Request $request): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return [];
    }
}
