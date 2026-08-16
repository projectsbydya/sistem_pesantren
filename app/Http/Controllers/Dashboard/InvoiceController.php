<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;

class InvoiceController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Show invoice detail to the owning tenant.
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('viewTenant', $invoice);

        $invoice->load(['subscription.plan', 'payments']);

        return view('dashboard.invoices.show', compact('invoice'));
    }

    /**
     * Generate (or retrieve) a payment link and redirect the tenant to it.
     *
     * Idempotent: if a pending payment link already exists it is reused.
     */
    public function pay(Invoice $invoice): RedirectResponse
    {
        $this->authorize('pay', $invoice);

        try {
            $paymentUrl = $this->subscriptionService->generatePaymentLink($invoice);

            return redirect()->away($paymentUrl);

        } catch (PaymentGatewayException $e) {
            return back()->with('error',
                'Gagal membuat tautan pembayaran: ' . $e->getMessage()
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
