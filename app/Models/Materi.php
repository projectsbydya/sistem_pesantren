<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materi extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'materi';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'jadwal_id',
        'ustadz_kelas_id',
        'kelas_id',
        'subject_id',
        'tanggal',
        'judul',
        'deskripsi',
        'tujuan_pembelajaran',
        'aktivitas',
        'referensi',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    const STATUS = ['draft', 'published', 'completed'];

    const STATUS_LABELS = [
        'draft'     => 'Draft',
        'published' => 'Dipublikasikan',
        'completed' => 'Selesai',
    ];

    const STATUS_COLORS = [
        'draft'     => 'gray',
        'published' => 'blue',
        'completed' => 'emerald',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'jadwal_id');
    }

    public function ustadzKelas(): BelongsTo
    {
        return $this->belongsTo(UstadzKelas::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

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

    public function scopeByJadwal($query, $jadwalId)
    {
        return $query->where('jadwal_id', $jadwalId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('tanggal', $date);
    }
}
