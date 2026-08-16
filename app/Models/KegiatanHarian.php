<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanHarian extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'kegiatan_harian';

    public const JENIS_SHOLAT = 'sholat';
    public const JENIS_TILAWAH = 'tilawah';
    public const JENIS_DZIKIR = 'dzikir';
    public const JENIS_SHOLAT_DHUHA = 'sholat_dhuha';
    public const JENIS_SHOLAT_TAHAJJUD = 'sholat_tahajjud';
    public const JENIS_SHOLAT_RAWATIB = 'sholat_rawatib';
    public const JENIS_MUROJAah = 'murojaah';
    public const JENIS_SETORAN = 'setoran';
    public const JENIS_PAGI = 'kegiatan_pagi';
    public const JENIS_SORE = 'kegiatan_sore';
    public const JENIS_MALAM = 'kegiatan_malam';

    public const KATEGORI_WAJIB = 'wajib';
    public const KATEGORI_SUNNAH = 'sunnah';
    public const KATEGORI_EKSTRA = 'ekstra';

    public const STATUS_TERJADWAL = 'terjadwal';
    public const STATUS_DILAKSANAKAN = 'dilaksanakan';
    public const STATUS_TIDAK_DILAKSANAKAN = 'tidak_dilaksanakan';

    public const JENIS_OPTIONS = [
        self::JENIS_SHOLAT,
        self::JENIS_TILAWAH,
        self::JENIS_DZIKIR,
        self::JENIS_SHOLAT_DHUHA,
        self::JENIS_SHOLAT_TAHAJJUD,
        self::JENIS_SHOLAT_RAWATIB,
        self::JENIS_MUROJAah,
        self::JENIS_SETORAN,
        self::JENIS_PAGI,
        self::JENIS_SORE,
        self::JENIS_MALAM,
    ];

    public const KATEGORI_OPTIONS = [
        self::KATEGORI_WAJIB,
        self::KATEGORI_SUNNAH,
        self::KATEGORI_EKSTRA,
    ];

    public const STATUS_OPTIONS = [
        self::STATUS_TERJADWAL,
        self::STATUS_DILAKSANAKAN,
        self::STATUS_TIDAK_DILAKSANAKAN,
    ];

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'jenis_kegiatan',
        'kategori',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'catatan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_mulai' => 'datetime:H:i',
        'waktu_selesai' => 'datetime:H:i',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_kegiatan', $jenis);
    }

    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('tanggal', [$from, $to]);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('tanggal', $date);
    }
}
