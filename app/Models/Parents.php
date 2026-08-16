<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Parents extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'parents';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'nik',
        'phone',
        'email',
        'address',
        'relationship',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship constants
    const RELATIONSHIP_FATHER = 'father';
    const RELATIONSHIP_MOTHER = 'mother';
    const RELATIONSHIP_GUARDIAN = 'guardian';

    // =========================================================================
    // Relations
    // =========================================================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function santri(): BelongsToMany
    {
        return $this->belongsToMany(Santri::class, 'parent_santri', 'parent_id', 'santri_id')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    public function primarySantri(): BelongsToMany
    {
        return $this->belongsToMany(Santri::class, 'parent_santri', 'parent_id', 'santri_id')
            ->wherePivot('is_primary', true)
            ->withPivot(['relationship', 'is_primary']);
    }

    // =========================================================================
    // Access Control
    // =========================================================================

    public function hasSantri(int $santriId): bool
    {
        return $this->santri()->withoutGlobalScopes()->where('santri.id', $santriId)->exists();
    }

    public function isPrimaryForSantri(int $santriId): bool
    {
        return $this->santri()
            ->withoutGlobalScopes()
            ->where('santri.id', $santriId)
            ->wherePivot('is_primary', true)
            ->exists();
    }
}
