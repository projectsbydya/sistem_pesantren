<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'nilai';

    // =========================================================================
    // Assessment types are defined in the assessment_types registry and configured
    // per-tenant/program via program_assessment_configs. This model only stores the
    // type code; labels/colors/order/active flags must be read from the registry.
    // =========================================================================

    protected $fillable = [
        'tenant_id',
        'program_id',
        'santri_id',
        'subject_id',
        'kelas_id',
        'ustadz_kelas_id',
        'tanggal',
        'assessment_type',
        'materi',
        'nilai',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nilai'   => 'float',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function ustadzKelas(): BelongsTo
    {
        return $this->belongsTo(UstadzKelas::class);
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

    public function scopeByAssessmentType($query, string $type)
    {
        return $query->where('assessment_type', $type);
    }
}
