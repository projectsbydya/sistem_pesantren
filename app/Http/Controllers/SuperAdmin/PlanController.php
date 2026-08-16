<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Plan::class);

        $plans = Plan::withCount('subscriptions')
            ->when($request->filled('billing_cycle'), fn ($q) => $q->where('billing_cycle', $request->billing_cycle))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', (bool) $request->is_active))
            ->orderBy('price')
            ->get();

        return view('dashboard.super-admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $this->authorize('create', Plan::class);

        return view('dashboard.super-admin.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Plan::class);

        $validated = $this->validatePlan($request);

        Plan::create($validated);

        return redirect()
            ->route('dashboard.super-admin.plans.index')
            ->with('success', "Plan {$validated['name']} berhasil dibuat.");
    }

    public function show(Plan $plan)
    {
        $this->authorize('view', $plan);

        $plan->load(['subscriptions.tenant']);

        return view('dashboard.super-admin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        $this->authorize('update', $plan);

        return view('dashboard.super-admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $validated = $this->validatePlan($request, $plan);

        $plan->update($validated);

        return redirect()
            ->route('dashboard.super-admin.plans.index')
            ->with('success', 'Plan berhasil diupdate.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()
            ->route('dashboard.super-admin.plans.index')
            ->with('success', 'Plan berhasil dihapus.');
    }

    public function toggleActive(Plan $plan): RedirectResponse
    {
        $this->authorize('toggleActive', $plan);

        $plan->update(['is_active' => !$plan->is_active]);

        $status = $plan->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Plan berhasil {$status}.");
    }

    private function validatePlan(Request $request, ?Plan $plan = null): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'code'             => [
                'required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/',
                $plan
                    ? Rule::unique('plans', 'code')->ignore($plan->id)
                    : Rule::unique('plans', 'code'),
            ],
            'description'      => ['nullable', 'string', 'max:1000'],
            'price'            => ['required', 'numeric', 'min:0'],
            'billing_cycle'    => ['required', Rule::in(Plan::BILLING_CYCLES)],
            'trial_days'       => ['required', 'integer', 'min:0', 'max:365'],
            'santri_limit'     => ['required', 'integer', 'min:0'],
            'user_limit'       => ['required', 'integer', 'min:0'],
            'branch_limit'     => ['required', 'integer', 'min:0'],
            'storage_limit_mb' => ['required', 'integer', 'min:0'],
            'features'         => ['nullable', 'array'],
            'is_active'        => ['boolean'],
        ]);
    }
}
