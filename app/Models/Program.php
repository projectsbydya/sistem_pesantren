<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_available_for_tenants',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available_for_tenants' => 'boolean',
    ];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function assessmentConfigs(): HasMany
    {
        return $this->hasMany(ProgramAssessmentConfig::class, 'program_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class);
    }

    public function ustadzKelas(): HasMany
    {
        return $this->hasMany(UstadzKelas::class);
    }

    /**
     * Get tenants that have this program enabled.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_programs')
            ->withPivot('is_active', 'activated_at', 'settings')
            ->withTimestamps();
    }

    /**
     * Scope: Get programs active for a specific tenant.
     */
    public function scopeActiveForTenant($query, int $tenantId)
    {
        return $query->whereHas('tenants', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->where('tenant_programs.is_active', true);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableForTenants($query)
    {
        return $query->where('is_available_for_tenants', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Check if program is in use (has kelas, subjects, or jadwal linked to it).
     * Used to prevent deactivating programs that are actively referenced.
     */
    public function isInUse(): bool
    {
        return $this->kelas()->exists()
            || $this->subjects()->exists()
            || $this->schedules()->exists();
    }
}
