<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggaran extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'pelanggaran';

    public const JENIS_RINGAN = 'ringan';
    public const JENIS_SEDANG = 'sedang';
    public const JENIS_BERAT = 'berat';

    public const STATUS_PENDING = 'pending';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';

    public const JENIS_OPTIONS = [
        self::JENIS_RINGAN,
        self::JENIS_SEDANG,
        self::JENIS_BERAT,
    ];

    public const STATUS_OPTIONS = [
        self::STATUS_PENDING,
        self::STATUS_DIPROSES,
        self::STATUS_SELESAI,
    ];

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'jenis',
        'kategori',
        'deskripsi',
        'tanggal',
        'lokasi',
        'pelapor_id',
        'status',
        'tindak_lanjut',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    public function sanksi(): HasMany
    {
        return $this->hasMany(Sanksi::class);
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('tanggal', [$from, $to]);
    }
}
