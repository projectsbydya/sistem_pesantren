<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ustadz extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'ustadz';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'bio',
        'phone',
        'role',
        'jam_per_minggu',
        'performa',
        'status',
    ];

    protected $casts = [
        'jam_per_minggu' => 'integer',
        'performa' => 'integer',
    ];

    // Role constants
    const ROLE_PENGAJAR = 'pengajar';
    const ROLE_WALI_KELAS = 'wali_kelas';
    const ROLE_KEPALA_PONPES = 'kepala_ponpes';
    const ROLE_BENDAHARA = 'bendahara';
    const ROLE_ADMIN = 'admin';

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_CUTI = 'cuti';

    /**
     * Role options for forms/display.
     */
    public static function getRoleOptions(): array
    {
        return [
            self::ROLE_PENGAJAR => 'Pengajar',
            self::ROLE_WALI_KELAS => 'Wali Kelas',
            self::ROLE_KEPALA_PONPES => 'Kepala Ponpes',
            self::ROLE_BENDAHARA => 'Bendahara',
            self::ROLE_ADMIN => 'Admin',
        ];
    }

    /**
     * Status options for forms/display.
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Nonaktif',
            self::STATUS_CUTI => 'Cuti',
        ];
    }

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

    public function ustadzKelas(): HasMany
    {
        return $this->hasMany(UstadzKelas::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function absensiUstadz(): HasMany
    {
        return $this->hasMany(AbsensiUstadz::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_ustadz')
            ->withTimestamps();
    }

    /**
     * Backward-compatible accessor: comma-separated subject names.
     */
    public function getSpecializationAttribute(): ?string
    {
        return $this->subjects->pluck('name')->implode(', ') ?: null;
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereHas('user', fn ($q) => $q->where('is_active', true));
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeHighPerforma($query, int $minPerforma = 80)
    {
        return $query->where('performa', '>=', $minPerforma);
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && ($this->user?->is_active ?? false);
    }

    public function getPerformaColor(): string
    {
        if ($this->performa === null) return 'gray';
        if ($this->performa >= 85) return 'emerald';
        if ($this->performa >= 70) return 'blue';
        return 'amber';
    }

    public function getPerformaLabel(): string
    {
        if ($this->performa === null) return 'Belum dinilai';
        if ($this->performa >= 85) return 'Sangat Baik';
        if ($this->performa >= 70) return 'Baik';
        return 'Perlu Perbaikan';
    }
}
