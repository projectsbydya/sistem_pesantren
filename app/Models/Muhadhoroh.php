<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Muhadhoroh — Modern Program Pack Entity
 *
 * Unified entity for speech/public-speaking management per type (muhadhoroh|public-speaking).
 * Follows the type-based architecture established by Vocabulary and Muhadatsah.
 *
 * SaaS Principles:
 * - Multi-tenancy: HasTenant trait enforces automatic tenant scoping
 * - Program isolation: program_id FK ensures program-scoped queries
 * - No hardcoded values: type handled via constants, no slug assumptions
 * - Policy: MuhadhorohPolicy gates all access
 */
class Muhadhoroh extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'muhadhorohs';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'santri_id',
        'type',
        'title',
        'theme_id',
        'language',
        'description',
        'score',
        'notes',
        'performed_at',
        'is_video_submission',
        'submission_url',
    ];

    protected $casts = [
        'score'              => 'integer',
        'performed_at'       => 'date',
        'is_video_submission' => 'boolean',
    ];

    const TYPES = ['muhadhoroh', 'public-speaking'];

    const TYPE_LABELS = [
        'muhadhoroh'     => 'Muhadhoroh (Pidato)',
        'public-speaking' => 'Public Speaking',
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

    public function theme(): BelongsTo
    {
        return $this->belongsTo(MuhadhorohTheme::class, 'theme_id');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
