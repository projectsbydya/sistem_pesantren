<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Muhadatsah — Modern Program Pack Entity
 *
 * Unified entity for conversation/speaking-practice management per type (arabic|english).
 * Follows the type-based architecture established by Vocabulary.
 *
 * SaaS Principles:
 * - Multi-tenancy: HasTenant trait enforces automatic tenant scoping
 * - Program isolation: program_id FK ensures program-scoped queries
 * - No hardcoded values: type handled via constants, no slug assumptions
 * - Policy: MuhadatsahPolicy gates all access
 */
class Muhadatsah extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'muhadatsahs';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'santri_id',
        'type',
        'topic',
        'content',
        'category',
        'score',
        'notes',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    const TYPES = ['arabic', 'english'];

    const TYPE_LABELS = [
        'arabic'  => 'Muhadatsah (Arab)',
        'english' => 'Conversation (Inggris)',
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

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
