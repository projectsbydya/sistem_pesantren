<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'package_name',
        'billing_cycle',
        'amount',
        'starts_at',
        'ends_at',
        'status',
        'trial_ends_at',
        'grace_period_ends_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'starts_at'            => 'datetime',
        'ends_at'              => 'datetime',
        'trial_ends_at'        => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'cancelled_at'         => 'datetime',
    ];

    // =========================================================================
    // Constants
    // =========================================================================

    const BILLING_CYCLES = ['monthly', 'yearly'];

    const STATUSES = ['trial', 'active', 'suspended', 'expired', 'cancelled'];

    const STATUS_LABELS = [
        'trial' => 'Trial',
        'active' => 'Aktif',
        'suspended' => 'Ditangguhkan',
        'expired' => 'Kadaluarsa',
        'cancelled' => 'Dibatalkan',
    ];

    const STATUS_COLORS = [
        'trial' => 'blue',
        'active' => 'emerald',
        'suspended' => 'amber',
        'expired' => 'rose',
        'cancelled' => 'gray',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeEndingSoon($query, int $days = 7)
    {
        return $query->where('ends_at', '<=', now()->addDays($days))
            ->where('ends_at', '>', now());
    }

    public function scopeInGracePeriod($query)
    {
        return $query->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '>', now());
    }

    // =========================================================================
    // Status Check Methods
    // =========================================================================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isInGracePeriod(): bool
    {
        return $this->grace_period_ends_at !== null
            && $this->grace_period_ends_at->isFuture();
    }

    public function isTrialExpired(): bool
    {
        return $this->trial_ends_at !== null
            && $this->trial_ends_at->isPast();
    }

    public function isEndingSoon(int $days = 7): bool
    {
        return $this->ends_at !== null
            && $this->ends_at->diffInDays(now()) <= $days
            && $this->ends_at->isFuture();
    }

    // =========================================================================
    // Action Methods
    // =========================================================================

    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    public function suspend(): bool
    {
        return $this->update(['status' => 'suspended']);
    }

    public function expire(): bool
    {
        return $this->update(['status' => 'expired']);
    }

    public function cancel(): bool
    {
        return $this->update([
            'status'       => 'cancelled',
            'cancelled_at' => $this->cancelled_at ?? now(),
        ]);
    }

    public function renew(array $attributes = []): bool
    {
        $newEndsAt = $this->calculateRenewalEndDate();

        return $this->update([
            'status' => 'active',
            'ends_at' => $newEndsAt,
            'grace_period_ends_at' => null,
            ...$attributes,
        ]);
    }

    public function extendGracePeriod(int $days): bool
    {
        return $this->update([
            'grace_period_ends_at' => now()->addDays($days),
        ]);
    }

    public function convertFromTrial(): bool
    {
        if (!$this->isTrial()) {
            return false;
        }

        $endsAt = $this->calculateRenewalEndDate();

        return $this->update([
            'status' => 'active',
            'trial_ends_at' => null,
            'starts_at' => now(),
            'ends_at' => $endsAt,
        ]);
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    private function calculateRenewalEndDate(): \Carbon\Carbon
    {
        $baseDate = $this->ends_at && $this->ends_at->isFuture()
            ? $this->ends_at
            : now();

        return $this->billing_cycle === 'yearly'
            ? $baseDate->copy()->addYear()
            : $baseDate->copy()->addMonth();
    }

    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getRemainingDays(): ?int
    {
        if (!$this->ends_at) {
            return null;
        }

        return now()->diffInDays($this->ends_at, false);
    }

    public function getDaysUntilTrialEnds(): ?int
    {
        if (!$this->trial_ends_at) {
            return null;
        }

        return now()->diffInDays($this->trial_ends_at, false);
    }

    // =========================================================================
    // Boot Methods
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        // Auto-set trial_ends_at if not provided and status is trial
        static::creating(function (self $subscription) {
            if ($subscription->status === 'trial' && !$subscription->trial_ends_at) {
                $subscription->trial_ends_at = now()->addDays(14); // Default 14 days trial
            }
        });

        // Update tenant's is_trial flag when subscription changes
        static::saved(function (self $subscription) {
            $subscription->syncTenantTrialStatus();
        });
    }

    private function syncTenantTrialStatus(): void
    {
        $tenant = $this->tenant;

        if (!$tenant) {
            return;
        }

        $isTrial = $this->isTrial() && !$this->isTrialExpired();

        if ($tenant->is_trial !== $isTrial) {
            $tenant->update(['is_trial' => $isTrial]);
        }
    }
}
