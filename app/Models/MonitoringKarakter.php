<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringKarakter extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'monitoring_karakter';

    public const ASPEK_AKHLAK = 'akhlak';
    public const ASPEK_DISIPLIN = 'disiplin';
    public const ASPEK_TANGGUNG_JAWAB = 'tanggung_jawab';
    public const ASPEK_KERJA_SAMA = 'kerja_sama';
    public const ASPEK_KEJujuran = 'kejujuran';
    public const ASPEK_KEMANDIRIAN = 'kemandirian';

    public const PREDIKAT_SANGAT_BAIK = 'sangat_baik';
    public const PREDIKAT_BAIK = 'baik';
    public const PREDIKAT_CUKUP = 'cukup';
    public const PREDIKAT_KURANG = 'kurang';

    public const ASPEK_OPTIONS = [
        self::ASPEK_AKHLAK,
        self::ASPEK_DISIPLIN,
        self::ASPEK_TANGGUNG_JAWAB,
        self::ASPEK_KERJA_SAMA,
        self::ASPEK_KEJujuran,
        self::ASPEK_KEMANDIRIAN,
    ];

    public const PREDIKAT_OPTIONS = [
        self::PREDIKAT_SANGAT_BAIK,
        self::PREDIKAT_BAIK,
        self::PREDIKAT_CUKUP,
        self::PREDIKAT_KURANG,
    ];

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'aspek',
        'skor',
        'predikat',
        'deskripsi',
        'tanggal',
        'periode',
        'dinilai_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'skor' => 'integer',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function dinilaiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    public function scopeByAspek($query, $aspek)
    {
        return $query->where('aspek', $aspek);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('tanggal', [$from, $to]);
    }

    public function scopeByPeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }

    /**
     * Calculate predikat based on skor.
     */
    public static function hitungPredikat(int $skor): string
    {
        return match (true) {
            $skor >= 85 => self::PREDIKAT_SANGAT_BAIK,
            $skor >= 70 => self::PREDIKAT_BAIK,
            $skor >= 60 => self::PREDIKAT_CUKUP,
            default => self::PREDIKAT_KURANG,
        };
    }
}
