<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\HasTenant;
use App\Models\Ustadz;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasTenant, HasRoles, HasApiTokens;

    /**
     * Name of the guard used for Spatie permission checks.
     */
    protected string $guard_name = 'web';

    // Role Constants
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_PARENT = 'parent';
    const ROLE_STUDENT = 'student';
    const ROLE_USTADZ = 'ustadz';
    const ROLE_BENDAHARA = 'bendahara';

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_changed_at',
        'tenant_id',
        'role',
        'is_super_admin',
        'is_active',
        'must_change_password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    // =========================================================================
    // Boot — Auto-sync Spatie role from legacy role column
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        $roleMap = [
            self::ROLE_ADMIN       => Role::TENANT_ADMIN,
            self::ROLE_SUPER_ADMIN => Role::SUPER_ADMIN,
            self::ROLE_PARENT      => Role::PARENT,
            self::ROLE_STUDENT     => Role::SANTRI,
            self::ROLE_USTADZ      => Role::USTADZ,
            self::ROLE_BENDAHARA   => Role::BENDAHARA,
        ];

        $ensureRole = function (User $user) use ($roleMap) {
            if (isset($roleMap[$user->role])) {
                $roleName   = $roleMap[$user->role];
                $spatieRole = \Spatie\Permission\Models\Role::firstOrCreate(
                    ['name' => $roleName, 'guard_name' => 'web']
                );
                $user->syncRoles([$spatieRole]);
            }
        };

        static::created($ensureRole);

        static::updated(function (User $user) use ($ensureRole) {
            if ($user->wasChanged('role')) {
                $ensureRole($user);
            }
        });
    }

    // =========================================================================
    // Role Check Methods — USER → ROLE (via pivot table)
    // =========================================================================

    /**
     * Check via is_super_admin flag OR Spatie role
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * Check via Spatie role
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::TENANT_ADMIN);
    }

    /**
     * Check via parent relation (USER → RELATION → RESOURCE)
     * Not via role string
     */
    public function isParent(): bool
    {
        return $this->parent !== null;
    }

    /**
     * Check via santri relation (USER → RELATION → RESOURCE)
     * Not via role string
     */
    public function isStudent(): bool
    {
        return $this->santri !== null;
    }

    /**
     * Check via ustadz relation (USER → RELATION → RESOURCE)
     * Not via role string
     */
    public function isUstadz(): bool
    {
        return $this->ustadz !== null;
    }

    /**
     * Check via Spatie role
     */
    public function isBendahara(): bool
    {
        return $this->hasRole(Role::BENDAHARA);
    }

    /**
     * Alias for isAdmin() — "tenant admin" (not super_admin).
     */
    public function isAdminTenant(): bool
    {
        return $this->isAdmin() && !$this->isSuperAdmin();
    }

    /**
     * True for any user that belongs to a tenant (non super_admin).
     */
    public function isTenantUser(): bool
    {
        return !$this->isSuperAdmin() && $this->tenant_id !== null;
    }

    /**
     * Get human-readable role label
     */
    public function getRoleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_PARENT => 'Orang Tua',
            self::ROLE_STUDENT => 'Santri',
            self::ROLE_USTADZ => 'Ustadz',
            self::ROLE_BENDAHARA => 'Bendahara',
            default => ucfirst($this->role),
        };
    }

    // =========================================================================
    // Relations
    // =========================================================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function santri(): HasOne
    {
        return $this->hasOne(Santri::class);
    }

    public function parent(): HasOne
    {
        return $this->hasOne(Parents::class);
    }

    public function ustadz(): HasOne
    {
        return $this->hasOne(Ustadz::class);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Get the santri_id attribute via relationship
     * Fixes: Santri::canBeAccessedBy() references $user->santri_id
     */
    public function getSantriIdAttribute(): ?int
    {
        return $this->santri?->id;
    }

    // =========================================================================
    // Access Control
    // =========================================================================

    public function ownsTenant(int $tenantId): bool
    {
        return $this->tenant_id !== null && (int) $this->tenant_id === $tenantId;
    }

    /**
     * Returns true only if this user belongs to the given tenant.
     * Super admin intentionally returns false — they manage tenants but
     * must NOT access operational tenant data (SaaS isolation requirement).
     */
    public function canAccessTenant(int $tenantId): bool
    {
        return $this->ownsTenant($tenantId);
    }

    /**
     * Get santri IDs that this user (as parent) has access to
     */
    public function getAccessibleSantriIds(): array
    {
        if ($this->isStudent() && $this->santri) {
            return [$this->santri->id];
        }

        if ($this->isParent() && $this->parent) {
            return $this->parent->santri()->withoutGlobalScopes()->pluck('santri.id')->toArray();
        }

        if ($this->hasRole(Role::TENANT_ADMIN) || $this->isSuperAdmin()) {
            return Santri::forTenant($this->tenant_id)->pluck('id')->toArray();
        }

        return [];
    }

    // =========================================================================
    // Password Lifecycle
    // =========================================================================

    /**
     * Mark password as changed and clear the must_change_password flag.
     * Call this after successful password update.
     */
    public function markPasswordChanged(): void
    {
        $this->update([
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);
    }

    /**
     * Force password change on next login.
     * Useful for admin-initiated password resets.
     */
    public function requirePasswordChange(): void
    {
        $this->update(['must_change_password' => true]);
    }
}
