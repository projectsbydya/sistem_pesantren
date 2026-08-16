<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Subscription Management Controller (Super Admin Only)
 *
 * All subscription operations are restricted to super admin.
 * Tenants cannot directly access or modify their subscription data.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {
    }

    /**
     * Display a listing of all subscriptions.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Subscription::class);

        $query = Subscription::with('tenant')->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by billing cycle
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Search by package name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('package_name', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();
        $tenants = Tenant::select('id', 'name')->orderBy('name')->get();

        return view('dashboard.super-admin.subscriptions.index', compact(
            'subscriptions',
            'tenants'
        ));
    }

    /**
     * Show the form for creating a new subscription.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Subscription::class);

        $tenants = Tenant::select('id', 'name', 'is_trial', 'trial_ends_at')
            ->orderBy('name')
            ->get();

        $preselectedTenant = $request->tenant_id ? Tenant::find($request->tenant_id) : null;

        return view('dashboard.super-admin.subscriptions.create', compact(
            'tenants',
            'preselectedTenant'
        ));
    }

    /**
     * Store a newly created subscription.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Subscription::class);

        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'package_name' => ['required', 'string', 'max:255'],
            'billing_cycle' => ['required', 'string', Rule::in(Subscription::BILLING_CYCLES)],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'string', Rule::in(Subscription::STATUSES)],
            'trial_ends_at' => ['nullable', 'date'],
            'grace_period_ends_at' => ['nullable', 'date'],
        ]);

        try {
            $subscription = $this->subscriptionService->create([
                'tenant_id' => $validated['tenant_id'],
                'package_name' => $validated['package_name'],
                'billing_cycle' => $validated['billing_cycle'],
                'amount' => $validated['amount'],
                'starts_at' => $validated['starts_at'] ?? now(),
                'ends_at' => $validated['ends_at'] ?? null,
                'status' => $validated['status'],
                'trial_ends_at' => $validated['trial_ends_at'] ?? null,
                'grace_period_ends_at' => $validated['grace_period_ends_at'] ?? null,
            ]);

            return redirect()
                ->route('dashboard.super-admin.subscriptions.index')
                ->with('success', "Subscription {$subscription->package_name} berhasil dibuat untuk tenant.");

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat subscription: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription)
    {
        $this->authorize('view', $subscription);

        $subscription->load('tenant');

        return view('dashboard.super-admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit(Subscription $subscription)
    {
        $this->authorize('update', $subscription);

        $subscription->load('tenant');
        $tenants = Tenant::select('id', 'name')->orderBy('name')->get();

        return view('dashboard.super-admin.subscriptions.edit', compact(
            'subscription',
            'tenants'
        ));
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('update', $subscription);

        $validated = $request->validate([
            'package_name' => ['required', 'string', 'max:255'],
            'billing_cycle' => ['required', 'string', Rule::in(Subscription::BILLING_CYCLES)],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'string', Rule::in(Subscription::STATUSES)],
            'trial_ends_at' => ['nullable', 'date'],
            'grace_period_ends_at' => ['nullable', 'date'],
        ]);

        try {
            $this->subscriptionService->update($subscription, $validated);

            return redirect()
                ->route('dashboard.super-admin.subscriptions.index')
                ->with('success', 'Subscription berhasil diupdate.');

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate subscription: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified subscription.
     */
    public function destroy(Subscription $subscription): RedirectResponse
    {
        $this->authorize('delete', $subscription);

        try {
            $this->subscriptionService->delete($subscription);

            return redirect()
                ->route('dashboard.super-admin.subscriptions.index')
                ->with('success', 'Subscription berhasil dihapus.');

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menghapus subscription: ' . $e->getMessage());
        }
    }

    /**
     * Activate a subscription.
     */
    public function activate(Subscription $subscription): RedirectResponse
    {
        $this->authorize('activate', $subscription);

        try {
            $this->subscriptionService->activate($subscription);

            return back()->with('success', 'Subscription berhasil diaktifkan.');

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal mengaktifkan subscription: ' . $e->getMessage());
        }
    }

    /**
     * Suspend a subscription.
     */
    public function suspend(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('suspend', $subscription);

        $reason = $request->input('reason');

        try {
            $this->subscriptionService->suspend($subscription, $reason);

            return back()->with('success', 'Subscription berhasil ditangguhkan.');

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menangguhkan subscription: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('cancel', $subscription);

        $reason = $request->input('reason');

        try {
            $this->subscriptionService->cancel($subscription, $reason);

            return back()->with('success', 'Subscription berhasil dibatalkan.');

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal membatalkan subscription: ' . $e->getMessage());
        }
    }

    /**
     * Renew a subscription.
     */
    public function renew(Subscription $subscription): RedirectResponse
    {
        $this->authorize('renew', $subscription);

        try {
            $this->subscriptionService->renew($subscription);

            return back()->with('success', 'Subscription berhasil diperpanjang.');

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal memperpanjang subscription: ' . $e->getMessage());
        }
    }

    /**
     * Convert trial to active subscription.
     */
    public function convertTrial(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('convertTrial', $subscription);

        $validated = $request->validate([
            'package_name' => ['required', 'string', 'max:255'],
            'billing_cycle' => ['required', 'string', Rule::in(Subscription::BILLING_CYCLES)],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->subscriptionService->convertTrial($subscription, $validated);

            return back()->with('success', 'Trial berhasil dikonversi menjadi subscription aktif.');

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal mengkonversi trial: ' . $e->getMessage());
        }
    }

    /**
     * Extend grace period.
     */
    public function extendGracePeriod(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('extendGracePeriod', $subscription);

        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        try {
            $this->subscriptionService->extendGracePeriod($subscription, $validated['days']);

            return back()->with('success', "Grace period diperpanjang {$validated['days']} hari.");

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal memperpanjang grace period: ' . $e->getMessage());
        }
    }

    /**
     * Manually suspend a tenant and its active subscription.
     */
    public function suspendTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('suspendTenant', $tenant);

        $notes = $request->input('notes');

        try {
            $this->subscriptionService->suspendTenant($tenant, $notes);

            return back()->with('success', "Tenant {$tenant->name} berhasil ditangguhkan.");

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menangguhkan tenant: ' . $e->getMessage());
        }
    }

    /**
     * Manually activate a tenant and its suspended subscription.
     */
    public function activateTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('activateTenant', $tenant);

        $notes = $request->input('notes');

        try {
            $this->subscriptionService->activateTenant($tenant, $notes);

            return back()->with('success', "Tenant {$tenant->name} berhasil diaktifkan.");

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal mengaktifkan tenant: ' . $e->getMessage());
        }
    }

    /**
     * Get subscriptions for a specific tenant (API/json response).
     */
    public function getForTenant(Request $request, int $tenantId)
    {
        $this->authorize('viewAny', Subscription::class);

        $subscriptions = $this->subscriptionService->getTenantSubscriptions($tenantId, [
            'status' => $request->status,
            'billing_cycle' => $request->billing_cycle,
        ]);

        return response()->json([
            'data' => $subscriptions,
        ]);
    }
}
