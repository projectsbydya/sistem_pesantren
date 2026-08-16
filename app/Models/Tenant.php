<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Tenant extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'address',
        'phone',
        'email',
        'logo',
        'is_active',
        'is_trial',
        'trial_ends_at',
        'plan',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_trial' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the santri for the tenant.
     */
    public function santri(): HasMany
    {
        return $this->hasMany(Santri::class);
    }

    /**
     * Get the settings for the tenant.
     */
    public function settings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    /**
     * Get active subscription.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();
    }

    public function currentPlan(): ?Plan
    {
        return $this->activeSubscription()?->plan;
    }

    /**
     * Get all subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if tenant has specific feature enabled.
     */
    public function hasFeature(string $featureName): bool
    {
        return $this->features()
            ->where('name', $featureName)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Get the features for the tenant.
     */
    public function features(): HasMany
    {
        return $this->hasMany(TenantFeature::class);
    }

    /**
     * Get programs enabled for this tenant.
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'tenant_programs')
            ->withPivot('is_active', 'activated_at', 'settings')
            ->withTimestamps();
    }

    /**
     * Get active programs for this tenant.
     */
    public function activePrograms(): BelongsToMany
    {
        return $this->programs()
            ->wherePivot('is_active', true)
            ->where('programs.is_active', true)
            ->orderBy('programs.name');
    }

    /**
     * Check if tenant has a specific program enabled.
     */
    public function hasProgram(string $programSlug): bool
    {
        return $this->programs()
            ->where('slug', $programSlug)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Scope: Active tenants only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Find by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
