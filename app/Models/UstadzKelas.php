<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UstadzKelas extends Model
{
    use HasTenant;

    protected $table = 'ustadz_kelas';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'ustadz_id',
        'kelas_id',
        'subject_id',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(Ustadz::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function elearning(): HasMany
    {
        return $this->hasMany(Elearning::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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

    public function scopeForUstadz($query, int $ustadzId)
    {
        return $query->where('ustadz_id', $ustadzId);
    }

    public function scopeForKelas($query, int $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    // =========================================================================
    // Validation Helpers
    // =========================================================================

    /**
     * Validate that subject and kelas belong to the same program.
     * Critical for maintaining data integrity across tenant boundaries.
     */
    public static function validateProgramOwnership(int $programId, int $kelasId, int $subjectId, ?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();
        $errors = [];

        // Verify kelas belongs to program and tenant
        $kelas = Kelas::where('id', $kelasId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$kelas) {
            $errors[] = 'Kelas tidak ditemukan atau tidak termasuk dalam tenant ini.';
        } elseif ($kelas->program_id !== $programId) {
            $errors[] = 'Kelas tidak termasuk dalam program yang dipilih.';
        }

        // Verify subject belongs to program and tenant
        $subject = Subject::where('id', $subjectId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$subject) {
            $errors[] = 'Mata pelajaran tidak ditemukan atau tidak termasuk dalam tenant ini.';
        } elseif ($subject->program_id !== $programId) {
            $errors[] = 'Mata pelajaran tidak termasuk dalam program yang dipilih.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if this assignment is valid for the given program.
     * Used for downstream validation in Jadwal, Absensi, Nilai.
     */
    public function isValidForProgram(int $programId): bool
    {
        if ($this->program_id !== $programId) {
            return false;
        }

        // Eager load if not already loaded
        $kelas = $this->kelas;
        $subject = $this->subject;

        if (!$kelas || !$subject) {
            return false;
        }

        return $kelas->program_id === $programId && $subject->program_id === $programId;
    }
}
