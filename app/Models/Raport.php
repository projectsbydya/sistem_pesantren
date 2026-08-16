<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Raport extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'raport';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'santri_id',
        'kelas_id',
        'semester',
        'tahun_ajaran',
        'status',
        'tanggal_diterbitkan',
        'catatan_umum',
        'sakit',
        'izin',
        'alpa',
        'total_hari_efektif',
        'kepala_pesantren',
        'nip_kepala',
    ];

    protected $casts = [
        'tanggal_diterbitkan' => 'date',
        'sakit' => 'integer',
        'izin' => 'integer',
        'alpa' => 'integer',
        'total_hari_efektif' => 'integer',
    ];

    const SEMESTER = ['ganjil', 'genap'];

    const STATUS = ['draft', 'published', 'archived'];

    const STATUS_LABELS = [
        'draft'     => 'Draft',
        'published' => 'Diterbitkan',
        'archived'  => 'Diarsipkan',
    ];

    const STATUS_COLORS = [
        'draft'     => 'gray',
        'published' => 'emerald',
        'archived'  => 'blue',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function nilaiRaport(): HasMany
    {
        return $this->hasMany(RaportNilai::class);
    }

    public function nilaiHafalan(): HasMany
    {
        return $this->hasMany(RaportHafalan::class);
    }

    public function nilaiComponents(): HasManyThrough
    {
        return $this->hasManyThrough(RaportNilaiComponent::class, RaportNilai::class);
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

    public function scopeBySantri($query, $santriId)
    {
        return $query->where('santri_id', $santriId);
    }

    public function scopeBySemester($query, $semester, $tahunAjaran)
    {
        return $query->where('semester', $semester)
                     ->where('tahun_ajaran', $tahunAjaran);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Calculate average nilai from all subjects
     */
    public function rataRata(): float
    {
        $nilai = $this->nilaiRaport;
        if ($nilai->isEmpty()) {
            return 0;
        }
        return round($nilai->avg('nilai_akhir'), 2);
    }

    /**
     * Get grade label based on average
     */
    public function predikat(): string
    {
        $rata = $this->rataRata();
        return match (true) {
            $rata >= 85 => 'A (Sangat Baik)',
            $rata >= 70 => 'B (Baik)',
            $rata >= 60 => 'C (Cukup)',
            $rata >= 50 => 'D (Kurang)',
            default => 'E (Sangat Kurang)',
        };
    }

    /**
     * Get ketuntasan status
     */
    public function statusKetuntasan(): string
    {
        $nilai = $this->nilaiRaport;
        if ($nilai->isEmpty()) {
            return 'Belum Dinilai';
        }

        $belumTuntas = $nilai->where('nilai_akhir', '<', 60)->count();
        return $belumTuntas === 0 ? 'Tuntas' : "Belum Tuntas ({$belumTuntas} mata pelajaran)";
    }
}
