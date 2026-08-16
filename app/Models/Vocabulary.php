<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vocabulary — Modern Program Pack Entity
 *
 * Unified entity for vocabulary management per type (arabic|english).
 * Follows the type-based architecture established by DiniyahHafalan.
 *
 * SaaS Principles:
 * - Multi-tenancy: HasTenant trait enforces automatic tenant scoping
 * - Program isolation: program_id FK ensures program-scoped queries
 * - No hardcoded values: type handled via constants, no slug assumptions
 * - Policy: VocabularyPolicy gates all access
 */
class Vocabulary extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'vocabularies';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'santri_id',
        'type',
        'word',
        'language',
        'translation',
        'example_sentence',
        'category',
        'score',
        'status',
        'notes',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    const TYPES = ['arabic', 'english'];

    const TYPE_LABELS = [
        'arabic'  => 'Mufrodat (Arab)',
        'english' => 'Vocabulary (Inggris)',
    ];

    const LANGUAGES = ['ar', 'en'];

    const STATUS = ['belum', 'proses', 'hafal'];

    const STATUS_LABELS = [
        'belum' => 'Belum Hafal',
        'proses' => 'Sedang Dihafal',
        'hafal'  => 'Sudah Hafal',
    ];

    const STATUS_COLORS = [
        'belum' => 'gray',
        'proses' => 'amber',
        'hafal'  => 'emerald',
    ];

    // =========================================================================
    // Static accessors — consume these instead of constants directly
    // =========================================================================

    public static function getTypes(): array
    {
        return self::TYPES;
    }

    public static function getLabels(): array
    {
        return self::TYPE_LABELS;
    }

    public static function getStatuses(): array
    {
        return self::STATUS;
    }

    public static function getStatusLabels(): array
    {
        return self::STATUS_LABELS;
    }

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

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
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

    public function isHafal(): bool
    {
        return $this->status === 'hafal';
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
