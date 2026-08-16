<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['tenant', 'subscription'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('tenant_id'), fn ($q) => $q->where('tenant_id', $request->tenant_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('tenant', fn ($tq) => $tq->where('name', 'like', "%{$request->search}%"));
            })
            ->latest();

        $invoices = $query->paginate(20)->withQueryString();
        $tenants  = Tenant::select('id', 'name')->orderBy('name')->get();

        return view('dashboard.super-admin.invoices.index', compact('invoices', 'tenants'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['tenant', 'subscription.plan', 'payments.confirmedBy']);

        return view('dashboard.super-admin.invoices.show', compact('invoice'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'subscription_id' => ['required', 'integer', 'exists:subscriptions,id'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'due_date'        => ['required', 'date', 'after_or_equal:today'],
            'period_label'    => ['nullable', 'string', 'max:100'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $subscription = Subscription::findOrFail($validated['subscription_id']);

        try {
            $invoice = $this->subscriptionService->generateInvoice($subscription, $validated);

            return redirect()
                ->route('dashboard.super-admin.invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} berhasil dibuat.");

        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Gagal membuat invoice: ' . $e->getMessage());
        }
    }

    public function cancel(Invoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Invoice berhasil dibatalkan.');
    }

    public function markPaid(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('markPaid', $invoice);

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Invoice berhasil ditandai lunas.');
    }
}
