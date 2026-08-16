<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kamar extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'kamar';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';
    public const STATUS_PENUH = 'penuh';

    public const STATUS_OPTIONS = [
        self::STATUS_AKTIF,
        self::STATUS_NONAKTIF,
        self::STATUS_PENUH,
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'kapasitas',
        'status',
        'lokasi',
        'fasilitas',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): HasMany
    {
        return $this->hasMany(Santri::class);
    }

    public function penempatan(): HasMany
    {
        return $this->hasMany(PenempatanKamar::class);
    }

    public function mutasiAsal(): HasMany
    {
        return $this->hasMany(MutasiKamar::class, 'kamar_asal_id');
    }

    public function mutasiTujuan(): HasMany
    {
        return $this->hasMany(MutasiKamar::class, 'kamar_tujuan_id');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeWithKapasitas($query)
    {
        return $query->withCount([
            'santri as terisi' => fn ($q) => $q->whereNotNull('kamar_id'),
        ]);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    public function getSisaKapasitasAttribute(): int
    {
        $terisi = $this->santri()->whereNotNull('kamar_id')->count();
        return max(0, $this->kapasitas - $terisi);
    }

    public function getIsPenuhAttribute(): bool
    {
        return $this->sisa_kapasitas <= 0;
    }
}
