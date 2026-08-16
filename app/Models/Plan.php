<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'billing_cycle',
        'trial_days',
        'santri_limit',
        'user_limit',
        'branch_limit',
        'storage_limit_mb',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'trial_days'       => 'integer',
        'santri_limit'     => 'integer',
        'user_limit'       => 'integer',
        'branch_limit'     => 'integer',
        'storage_limit_mb' => 'integer',
        'features'         => 'array',
        'is_active'        => 'boolean',
    ];

    // =========================================================================
    // Constants
    // =========================================================================

    const BILLING_CYCLES = ['monthly', 'yearly'];

    // =========================================================================
    // Relations
    // =========================================================================

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMonthly($query)
    {
        return $query->where('billing_cycle', 'monthly');
    }

    public function scopeYearly($query)
    {
        return $query->where('billing_cycle', 'yearly');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function hasFeature(string $key): bool
    {
        return isset($this->features[$key]) && $this->features[$key] === true;
    }

    public function getFeature(string $key, mixed $default = null): mixed
    {
        return $this->features[$key] ?? $default;
    }

    public function isUnlimited(): bool
    {
        return $this->santri_limit === 0;
    }

    public function getFormattedPrice(): string
    {
        return 'Rp ' . number_format((float) $this->price, 0, ',', '.');
    }
}
