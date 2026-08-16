<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\SaasPayment;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaasPaymentController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', SaasPayment::class);

        $payments = SaasPayment::with(['invoice.tenant'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.super-admin.payments.index', compact('payments'));
    }

    public function show(SaasPayment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load(['invoice.tenant', 'invoice.subscription.plan', 'confirmedBy']);

        return view('dashboard.super-admin.payments.show', compact('payment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SaasPayment::class);

        $validated = $request->validate([
            'invoice_id'     => ['required', 'integer', 'exists:invoices,id'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(SaasPayment::PAYMENT_METHODS)],
            'transfer_proof' => ['nullable', 'string', 'max:500'],
            'reference_id'   => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        try {
            $payment = $this->subscriptionService->recordPayment($invoice, $validated);

            return redirect()
                ->route('dashboard.super-admin.payments.show', $payment)
                ->with('success', 'Pembayaran berhasil dicatat.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    public function confirm(Request $request, SaasPayment $payment): RedirectResponse
    {
        $this->authorize('confirm', $payment);

        $notes = $request->input('notes');

        try {
            $this->subscriptionService->confirmPayment($payment, $notes);

            return back()->with('success', 'Pembayaran berhasil dikonfirmasi.');

        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal mengkonfirmasi pembayaran: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, SaasPayment $payment): RedirectResponse
    {
        $this->authorize('reject', $payment);

        $notes = $request->validate(['notes' => ['nullable', 'string', 'max:500']])['notes'] ?? null;

        try {
            $this->subscriptionService->rejectPayment($payment, $notes);

            return back()->with('success', 'Pembayaran berhasil ditolak.');

        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal menolak pembayaran: ' . $e->getMessage());
        }
    }

    public function destroy(SaasPayment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $payment->delete();

        return redirect()
            ->route('dashboard.super-admin.payments.index')
            ->with('success', 'Pembayaran berhasil dihapus.');
    }
}
