<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DiniyahMonitoring — Unified Entity for Monitoring Management
 *
 * ARCHITECTURE FROZEN: This single entity handles all monitoring types (sholat, adab, akhlak)
 * via the 'type' column. Legacy separate entities (DiniyahMonitoringSholat, DiniyahMonitoringAdab,
 * DiniyahMonitoringAkhlak) have been deprecated.
 *
 * History:
 * - 2026-06-14: Consolidated from 3 separate entities into unified entity
 * - Migration: 2026_06_14_100001_create_diniyah_monitorings_table.php
 *
 * SaaS Principles:
 * - Multi-tenancy: HasTenant trait enforces automatic tenant scoping
 * - Program isolation: program_id FK ensures program-scoped queries
 * - No hardcoded values: type handled via constants, no slug assumptions
 * - Policy: DiniyahMonitoringPolicy gates all access
 *
 * @frozen 2026-06-14
 */
class DiniyahMonitoring extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'diniyah_monitorings';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'santri_id',
        'type',
        'date',
        'aspect',
        'category',
        'status',
        'score',
        'flag',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'score' => 'integer',
        'flag' => 'boolean',
    ];

    const TYPES = ['sholat', 'adab', 'akhlak'];

    const TYPE_LABELS = [
        'sholat' => 'Monitoring Sholat',
        'adab'   => 'Monitoring Adab',
        'akhlak' => 'Monitoring Akhlak',
    ];

    // Sholat specific
    const SHOLAT_TIMES = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];

    const SHOLAT_TIME_LABELS = [
        'subuh'   => 'Subuh',
        'dzuhur'  => 'Dzuhur',
        'ashar'   => 'Ashar',
        'maghrib' => 'Maghrib',
        'isya'    => 'Isya',
    ];

    const SHOLAT_STATUSES = ['hadir', 'tidak_hadir', 'terlambat'];

    const SHOLAT_STATUS_LABELS = [
        'hadir'       => 'Hadir',
        'tidak_hadir' => 'Tidak Hadir',
        'terlambat'   => 'Terlambat',
    ];

    // Adab & Akhlak statuses
    const AKHLAK_STATUSES = ['sangat_baik', 'baik', 'cukup', 'kurang'];

    const AKHLAK_STATUS_LABELS = [
        'sangat_baik' => 'Sangat Baik',
        'baik'        => 'Baik',
        'cukup'       => 'Cukup',
        'kurang'      => 'Kurang',
    ];

    const AKHLAK_CATEGORIES = [
        'akhlak_kepada_allah'  => 'Akhlak kepada Allah',
        'akhlak_kepada_rasul'  => 'Akhlak kepada Rasul',
        'akhlak_kepada_sesama' => 'Akhlak kepada Sesama',
        'akhlak_kepada_diri'   => 'Akhlak kepada Diri Sendiri',
        'akhlak_kepada_alam'   => 'Akhlak kepada Alam',
    ];

    const STATUS_COLORS = [
        'hadir'       => 'emerald',
        'tidak_hadir' => 'red',
        'terlambat'   => 'amber',
        'sangat_baik' => 'emerald',
        'baik'        => 'blue',
        'cukup'       => 'amber',
        'kurang'      => 'red',
    ];

    const SCORE_LABELS = [
        4 => 'Sangat Baik',
        3 => 'Baik',
        2 => 'Cukup',
        1 => 'Kurang',
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

    public function scopeByDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
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

    public function getStatusLabel(): string
    {
        $labels = array_merge(self::SHOLAT_STATUS_LABELS, self::AKHLAK_STATUS_LABELS);
        return $labels[$this->status] ?? $this->status;
    }

    public function getScoreLabel(): ?string
    {
        return self::SCORE_LABELS[$this->score] ?? null;
    }

    public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function isSholat(): bool
    {
        return $this->type === 'sholat';
    }

    public function isAdab(): bool
    {
        return $this->type === 'adab';
    }

    public function isAkhlak(): bool
    {
        return $this->type === 'akhlak';
    }
}
