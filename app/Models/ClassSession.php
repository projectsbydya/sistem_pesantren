<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSession extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'class_sessions';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'schedule_id',
        'ustadz_id',
        'session_date',
        'status',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'started_at'   => 'datetime',
        'ended_at'     => 'datetime',
    ];

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ONGOING   = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_HOLIDAY   = 'holiday';

    const STATUS = [
        self::STATUS_SCHEDULED,
        self::STATUS_ONGOING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_HOLIDAY,
    ];

    const STATUS_LABELS = [
        self::STATUS_SCHEDULED => 'Terjadwal',
        self::STATUS_ONGOING   => 'Berlangsung',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
        self::STATUS_HOLIDAY   => 'Libur',
    ];

    const STATUS_COLORS = [
        self::STATUS_SCHEDULED => 'default',
        self::STATUS_ONGOING   => 'info',
        self::STATUS_COMPLETED => 'success',
        self::STATUS_CANCELLED => 'danger',
        self::STATUS_HOLIDAY   => 'warning',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(Ustadz::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function absensiSantri(): HasMany
    {
        return $this->hasMany(AbsensiSantri::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeByProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeBySchedule($query, int $scheduleId)
    {
        return $query->where('schedule_id', $scheduleId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('session_date', $date);
    }

    public function scopeByDateRange($query, string $start, string $end)
    {
        return $query->whereBetween('session_date', [$start, $end]);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }
}
