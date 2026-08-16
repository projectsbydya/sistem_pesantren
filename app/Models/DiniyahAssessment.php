<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DiniyahAssessment — Unified Entity for Assessment Management
 *
 * ARCHITECTURE FROZEN: This single entity handles all assessment types (keagamaan, akhlak)
 * via the 'type' column. Legacy separate entities (DiniyahNilaiKeagamaan, DiniyahNilaiAkhlak)
 * have been deprecated.
 *
 * History:
 * - 2026-06-14: Consolidated from 2 separate entities into unified entity
 * - Migration: 2026_06_14_100002_create_diniyah_assessments_table.php
 *
 * SaaS Principles:
 * - Multi-tenancy: HasTenant trait enforces automatic tenant scoping
 * - Program isolation: program_id FK ensures program-scoped queries
 * - No hardcoded values: type handled via constants, no slug assumptions
 * - Policy: DiniyahAssessmentPolicy gates all access
 *
 * @frozen 2026-06-14
 */
class DiniyahAssessment extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'diniyah_assessments';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'santri_id',
        'type',
        'aspect',
        'score',
        'notes',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    protected $appends = ['predikat'];

    const TYPES = ['keagamaan', 'akhlak'];

    const TYPE_LABELS = [
        'keagamaan' => 'Nilai Keagamaan',
        'akhlak'    => 'Nilai Akhlak',
    ];

    // Predikat scale
    const PREDIKAT = [
        'A' => 'Amat Baik',
        'B' => 'Baik',
        'C' => 'Cukup',
        'D' => 'Kurang',
    ];

    const PREDIKAT_COLORS = [
        'A' => 'emerald',
        'B' => 'blue',
        'C' => 'amber',
        'D' => 'red',
    ];

    // Default score ranges for predikat
    const PREDIKAT_RANGES = [
        'A' => [85, 100],
        'B' => [70, 84],
        'C' => [60, 69],
        'D' => [0, 59],
    ];

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeBySantri($query, int $santriId)
    {
        return $query->where('santri_id', $santriId);
    }

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function getTypeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getPredikatAttribute(): string
    {
        return self::hitungPredikat((float) $this->score);
    }

    public function getPredikatLabel(): ?string
    {
        return self::PREDIKAT[$this->predikat] ?? null;
    }

    public function getPredikatColor(): string
    {
        return self::PREDIKAT_COLORS[$this->predikat] ?? 'gray';
    }

    public static function hitungPredikat(float $score): string
    {
        foreach (self::PREDIKAT_RANGES as $predikat => [$min, $max]) {
            if ($score >= $min && $score <= $max) {
                return $predikat;
            }
        }
        return 'D';
    }

    public function isKeagamaan(): bool
    {
        return $this->type === 'keagamaan';
    }

    public function isAkhlak(): bool
    {
        return $this->type === 'akhlak';
    }
}
