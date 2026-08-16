<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DiniyahHafalan — Unified Entity for Hafalan Management
 *
 * ARCHITECTURE FROZEN: This single entity handles all hafalan types (doa, hadits, surat)
 * via the 'type' column. Legacy separate entities (DiniyahHafalanDoa, DiniyahHafalanHadits,
 * DiniyahHafalanSurat) have been removed.
 *
 * History:
 * - 2026-06-14: Consolidated from 3 separate entities into unified entity
 * - Migration: 2026_06_14_100000_create_diniyah_hafalans_table.php
 *
 * SaaS Principles:
 * - Multi-tenancy: HasTenant trait enforces automatic tenant scoping
 * - Program isolation: program_id FK ensures program-scoped queries
 * - No hardcoded values: type handled via constants, no slug assumptions
 * - Policy: DiniyahHafalanPolicy gates all access
 *
 * @frozen 2026-06-14
 */
class DiniyahHafalan extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'diniyah_hafalans';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'santri_id',
        'type',
        'title',
        'target',
        'achievement',
        'status',
        'notes',
    ];

    protected $casts = [
        'target'      => 'string',
        'achievement' => 'string',
    ];

    const TYPES = ['doa', 'hadits', 'surat'];

    const TYPE_LABELS = [
        'doa'    => 'Doa',
        'hadits' => 'Hadits',
        'surat'  => 'Surat',
    ];

    const STATUS = ['belum', 'proses', 'selesai'];

    const STATUS_LABELS = [
        'belum'   => 'Belum',
        'proses'  => 'Dalam Proses',
        'selesai' => 'Selesai',
    ];

    const STATUS_COLORS = [
        'belum'   => 'gray',
        'proses'  => 'amber',
        'selesai' => 'emerald',
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

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['belum', 'proses']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'selesai');
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

    public function getProgressPercentage(): int
    {
        if (empty($this->target)) {
            return $this->status === 'selesai' ? 100 : 0;
        }

        $targetWords = str_word_count(strip_tags($this->target));
        $achievementWords = str_word_count(strip_tags($this->achievement ?? ''));

        if ($targetWords === 0) {
            return $this->status === 'selesai' ? 100 : 0;
        }

        $percentage = (int) round(($achievementWords / $targetWords) * 100);
        return min(100, max(0, $percentage));
    }

    public function isCompleted(): bool
    {
        return $this->status === 'selesai';
    }

    public function markAsComplete(): void
    {
        $this->update(['status' => 'selesai']);
    }
}
