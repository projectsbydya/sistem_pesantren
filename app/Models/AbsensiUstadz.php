<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiUstadz extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'absensi_ustadz';

    protected $fillable = [
        'tenant_id',
        'schedule_id',
        'ustadz_id',
        'tanggal',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    const STATUS = ['hadir', 'izin', 'sakit', 'alpa'];

    const STATUS_LABELS = [
        'hadir' => 'Hadir',
        'izin'  => 'Izin',
        'sakit' => 'Sakit',
        'alpa'  => 'Alpa',
    ];

    const STATUS_COLORS = [
        'hadir' => 'emerald',
        'izin'  => 'blue',
        'sakit' => 'amber',
        'alpa'  => 'red',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(Ustadz::class);
    }
}
