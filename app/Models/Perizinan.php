<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perizinan extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'perizinan';

    public const JENIS_PULANG = 'pulang';
    public const JENIS_KELUAR = 'keluar';

    public const STATUS_PENDING = 'pending';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_KEMBALI = 'kembali';

    public const JENIS_OPTIONS = [
        self::JENIS_PULANG,
        self::JENIS_KELUAR,
    ];

    public const STATUS_OPTIONS = [
        self::STATUS_PENDING,
        self::STATUS_DISETUJUI,
        self::STATUS_DITOLAK,
        self::STATUS_KEMBALI,
    ];

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'jenis',
        'alasan',
        'keterangan',
        'tanggal_mulai',
        'tanggal_selesai',
        'destinasi',
        'penjemput',
        'no_hp_penjemput',
        'status',
        'diajukan_oleh',
        'disetujui_oleh',
        'tanggal_persetujuan',
        'tanggal_kembali',
        'catatan_keamanan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_persetujuan' => 'datetime',
        'tanggal_kembali' => 'datetime',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_DISETUJUI]);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('tanggal_mulai', [$from, $to]);
    }
}
