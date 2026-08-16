<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Santri;
use App\Models\Ustadz;
use App\Models\UstadzKelas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HafalanKitab extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'hafalan_kitab';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'ustadz_kelas_id',
        'tanggal',
        'nama_kitab',
        'bab',
        'halaman',
        'nilai',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nilai'   => 'float',
    ];

    const STATUS = ['belum', 'proses', 'lulus'];

    const STATUS_LABELS = [
        'belum'  => 'Belum',
        'proses' => 'Proses',
        'lulus'  => 'Lulus',
    ];

    const STATUS_COLORS = [
        'belum'  => 'gray',
        'proses' => 'amber',
        'lulus'  => 'emerald',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function ustadzKelas(): BelongsTo
    {
        return $this->belongsTo(UstadzKelas::class);
    }

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(Ustadz::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'jadwal_id');
    }
}
