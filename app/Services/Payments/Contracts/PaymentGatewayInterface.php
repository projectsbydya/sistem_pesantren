<?php

declare(strict_types=1);

namespace App\Services\Payments\Contracts;

use App\Models\Invoice;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use Illuminate\Http\Request;

/**
 * Payment Gateway Interface
 *
 * Contract for payment gateway provider implementations.
 * All gateways must implement this interface for a swappable architecture.
 */
interface PaymentGatewayInterface
{
    /**
     * Create a payment link for the given invoice.
     *
     * Returns a normalised array with at minimum:
     *   - 'external_id'   (string)  — gateway-assigned transaction identifier
     *   - 'payment_url'   (string)  — URL the customer should be redirected to
     *   - 'expires_at'    (string|null) — ISO-8601 expiry datetime or null
     *   - 'raw'           (array)   — full raw gateway response
     *
     * @param  Invoice $invoice
     * @return array<string, mixed>
     * @throws PaymentGatewayException
     */
    public function createPaymentLink(Invoice $invoice): array;

    /**
     * Retrieve the current status of a transaction from the gateway.
     *
     * Returns a normalised array with at minimum:
     *   - 'external_id'  (string)
     *   - 'status'       (string) — one of: pending | paid | failed | expired
     *   - 'amount'       (int|float)
     *   - 'paid_at'      (string|null) — ISO-8601 or null
     *   - 'raw'          (array)  — full raw gateway response
     *
     * @param  string $externalId  Gateway-assigned transaction identifier
     * @return array<string, mixed>
     * @throws PaymentGatewayException
     */
    public function getPaymentStatus(string $externalId): array;

    /**
     * Verify that an inbound webhook request originates from the gateway.
     *
     * Implementations should validate signatures, tokens, or IPs as required
     * by the gateway provider. Must NOT throw — return false on any failure.
     *
     * @param  Request $request
     * @return bool
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Get the canonical gateway driver name (e.g. 'midtrans', 'xendit', 'null').
     */
    public function getGatewayName(): string;

    /**
     * Check whether the gateway is fully configured and ready to use.
     */
    public function isConfigured(): bool;
}
