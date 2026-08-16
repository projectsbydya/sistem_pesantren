<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class AbsensiSantri extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'absensi_santri';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'jadwal_id',
        'class_session_id',
        'santri_id',
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
    // Scopes
    // =========================================================================

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByProgramSlug($query, $slug)
    {
        return $query->whereHas('program', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    // =========================================================================
    // Relations
    // =========================================================================

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'jadwal_id');
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function ustadz()
    {
        return $this->hasOneThrough(
            Ustadz::class,
            Schedule::class,
            'id',        // jadwal.id
            'id',        // ustadz.id
            'jadwal_id', // absensi_santri.jadwal_id
            'ustadz_id'  // jadwal.ustadz_id
        );
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
