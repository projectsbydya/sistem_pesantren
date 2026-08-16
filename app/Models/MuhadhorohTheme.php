<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MuhadhorohTheme — Tenant-scoped reusable theme catalogue for Muhadhoroh.
 *
 * Tenants define themes once per program and reuse across all santri.
 * HasTenant enforces automatic tenant scoping on every query.
 */
class MuhadhorohTheme extends Model
{
    use HasTenant;

    protected $table = 'muhadhoroh_themes';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    // =========================================================================
    // Relations
    // =========================================================================

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function muhadhorohs(): HasMany
    {
        return $this->hasMany(Muhadhoroh::class, 'theme_id');
    }
}
