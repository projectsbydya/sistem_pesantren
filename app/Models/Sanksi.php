<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanksi extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'sanksi';

    public const JENIS_PERINGATAN = 'peringatan';
    public const JENIS_TUGAS = 'tugas';
    public const JENIS_SKORSING = 'skorsing';
    public const JENIS_DIKEMBALIKAN = 'dikembalikan';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const JENIS_OPTIONS = [
        self::JENIS_PERINGATAN,
        self::JENIS_TUGAS,
        self::JENIS_SKORSING,
        self::JENIS_DIKEMBALIKAN,
    ];

    public const STATUS_OPTIONS = [
        self::STATUS_AKTIF,
        self::STATUS_SELESAI,
        self::STATUS_DIBATALKAN,
    ];

    protected $fillable = [
        'tenant_id',
        'pelanggaran_id',
        'jenis',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'hasil_evaluasi',
        'diberikan_oleh',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pelanggaran(): BelongsTo
    {
        return $this->belongsTo(Pelanggaran::class);
    }

    public function diberikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diberikan_oleh');
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }
}
