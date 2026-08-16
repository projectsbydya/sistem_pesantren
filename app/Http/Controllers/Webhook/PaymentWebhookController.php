<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Payment Gateway Webhook Handler
 *
 * Receives inbound POST notifications from payment gateways (Xendit, Midtrans).
 * This endpoint is intentionally:
 *   - Unauthenticated (no Laravel session / Sanctum)
 *   - CSRF-exempt (registered in bootstrap/app.php or web.php without VerifyCsrfToken)
 *   - Always returns HTTP 200 so the gateway stops retrying on transient errors
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle an inbound webhook for the given gateway driver.
     *
     * Route: POST /webhooks/payment/{gateway}
     *   {gateway} must match a configured driver: 'xendit' | 'midtrans'
     */
    public function handle(Request $request, string $gateway): JsonResponse
    {
        Log::info('Webhook received', [
            'gateway' => $gateway,
            'ip'      => $request->ip(),
        ]);

        // ── 1. Resolve the gateway driver ────────────────────────────────────
        try {
            $gatewayDriver = PaymentGatewayFactory::make($gateway);
        } catch (\Throwable $e) {
            Log::warning('Webhook: unknown gateway driver', [
                'gateway' => $gateway,
                'error'   => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Unknown gateway'], 200);
        }

        // ── 2. Verify the request signature / token ───────────────────────────
        if (! $gatewayDriver->verifyWebhook($request)) {
            Log::warning('Webhook: signature verification failed', [
                'gateway' => $gateway,
                'ip'      => $request->ip(),
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ── 3. Extract external_id from the payload ───────────────────────────
        $externalId = $this->extractExternalId($request, $gateway);

        if (empty($externalId)) {
            Log::warning('Webhook: could not extract external_id', [
                'gateway' => $gateway,
                'payload' => $request->all(),
            ]);
            return response()->json(['message' => 'Missing external_id'], 200);
        }

        // ── 4. Orchestrate confirm / reject / skip ────────────────────────────
        $action = $this->subscriptionService->handleWebhookPayment($externalId, $gateway);

        Log::info('Webhook processed', [
            'gateway'     => $gateway,
            'external_id' => $externalId,
            'action'      => $action,
        ]);

        return response()->json(['message' => 'OK', 'action' => $action], 200);
    }

    /**
     * Extract the gateway transaction identifier from the request payload.
     *
     * Each gateway sends the reference ID under a different field name:
     *   - Xendit   : 'id'       (invoice object payload)
     *   - Midtrans : 'order_id' (notification payload)
     */
    private function extractExternalId(Request $request, string $gateway): string
    {
        return match ($gateway) {
            'xendit'   => (string) $request->input('id', ''),
            'midtrans' => (string) $request->input('order_id', ''),
            default    => '',
        };
    }
}
