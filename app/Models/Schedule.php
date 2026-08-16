<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'jadwal';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'ustadz_kelas_id',
        'kelas_id',
        'subject_id',
        'mata_pelajaran',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'kelas',
    ];

    const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];

    protected static function booted(): void
    {
        static::saving(function (self $schedule) {
            if ($schedule->ustadzKelas) {
                $schedule->ustadz_id = $schedule->ustadzKelas->ustadz_id;
            }
        });
    }

    // =========================================================================
    // Relations
    // =========================================================================

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(Ustadz::class);
    }

    public function ustadzKelas(): BelongsTo
    {
        return $this->belongsTo(UstadzKelas::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(AbsensiSantri::class, 'jadwal_id');
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'schedule_id');
    }

    public function absensiUstadz(): HasMany
    {
        return $this->hasMany(AbsensiUstadz::class, 'schedule_id');
    }

    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class, 'jadwal_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Detect overlapping schedules for the same ustadz on the same day.
     * Excludes the given $exceptId (used on update to ignore self).
     */
    public function scopeConflicting($query, int $ustadzKelasId, string $hari, string $jamMulai, string $jamSelesai, ?int $exceptId = null)
    {
        $query->where('hari', $hari)
              ->where('ustadz_kelas_id', $ustadzKelasId)
              ->where(function ($q) use ($jamMulai, $jamSelesai) {
                  // Overlap: existing.start < new.end AND existing.end > new.start
                  $q->where('jam_mulai', '<', $jamSelesai)
                    ->where('jam_selesai', '>', $jamMulai);
              });

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query;
    }

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
}
