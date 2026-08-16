<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Santri extends Model
{
    use HasFactory;
    use HasTenant; // Enables automatic tenant filtering and injection

    protected $table = 'santri';

    protected $fillable = [
        'tenant_id',
        'nis',
        'name',
        'gender',
        'birth_date',
        'address',
        'status',
        'school_level',
        'school_name',
        'kelas_id',
        'kamar_id',
        'is_mondok',
        'wali_id',
        'parent_id',
        'user_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_mondok'  => 'boolean',
    ];

    // =========================================================================
    // Core Relations
    // =========================================================================

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function hafalanQuran(): HasMany
    {
        return $this->hasMany(HafalanQuran::class);
    }

    public function hafalanKitab(): HasMany
    {
        return $this->hasMany(HafalanKitab::class);
    }

    public function targetHafalan(): HasMany
    {
        return $this->hasMany(TargetHafalan::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryParent(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(SantriProgram::class);
    }
    
    // =========================================================================
    // Parent Relations (Many-to-Many)
    // =========================================================================

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Parents::class, 'parent_santri', 'santri_id', 'parent_id')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    public function primaryParents(): BelongsToMany
    {
        return $this->belongsToMany(Parents::class, 'parent_santri', 'santri_id', 'parent_id')
            ->wherePivot('is_primary', true)
            ->withPivot(['relationship', 'is_primary']);
    }

    // =========================================================================
    // Access Control Scopes
    // =========================================================================

    /**
     * Scope: Filter by parent ID (for parent access control)
     */
    public function scopeForParent($query, int $parentId)
    {
        return $query->whereHas('parents', function ($q) use ($parentId) {
            $q->where('parents.id', $parentId);
        });
    }

    /**
     * Scope: Filter by array of santri IDs (for parent with multiple children)
     */
    public function scopeForSantriIds($query, array $santriIds)
    {
        return $query->whereIn('id', $santriIds);
    }

    /**
     * Scope: Get santri with NIS in specific tenant
     */
    public function scopeByNis($query, string $nis, ?int $tenantId = null)
    {
        $query = $query->where('nis', $nis);
        
        if ($tenantId) {
            $query = $query->forTenant($tenantId);
        }
        
        return $query;
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Check if santri can be accessed by user (parent, admin, or santri itself)
     */
    public function canBeAccessedBy(User $user): bool
    {
        // Super admin can access all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Must be same tenant
        if ($user->tenant_id !== $this->tenant_id) {
            return false;
        }

        // Admin can access all in their tenant
        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return true;
        }

        // Santri can only access themselves
        if ($user->isStudent() && $user->santri_id === $this->id) {
            return true;
        }

        // Parent can only access their children
        if ($user->isParent() && $user->parent) {
            return $this->parents()->withoutGlobalScopes()->where('parents.id', $user->parent->id)->exists();
        }

        return false;
    }

    /**
     * Get the santri card.
     */
    public function card(): HasOne
    {
        return $this->hasOne(SantriCard::class, 'santri_id');
    }

    /**
     * Get the education history.
     */
    public function riwayatPendidikan(): HasMany
    {
        return $this->hasMany(RiwayatPendidikan::class, 'santri_id');
    }

    /**
     * Get the grades.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'santri_id');
    }

    /**
     * Get the attendance records.
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'santri_id');
    }

    /**
     * Get the bills.
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'santri_id');
    }

    /**
     * Get the wallet.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'santri_id');
    }

    /**
     * Get the room memberships.
     */
    public function roomMembers(): HasMany
    {
        return $this->hasMany(RoomMember::class, 'santri_id');
    }

    /**
     * Get current room (if any).
     */
    public function currentRoom(): ?Room
    {
        $member = $this->roomMembers()
            ->whereNull('left_at')
            ->first();

        return $member?->room;
    }

    /**
     * Scope: Get active santri only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Filter by gender.
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }
    
}