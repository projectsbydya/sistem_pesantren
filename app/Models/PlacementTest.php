<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlacementTest extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'placement_tests';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'type',
        'title',
        'description',
        'date',
        'max_score',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'max_score' => 'integer',
    ];

    const TYPES = ['english', 'arabic'];

    const TYPE_LABELS = [
        'english' => 'English Placement Test',
        'arabic' => 'Arabic Placement Test',
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }

    public static function getLabels(): array
    {
        return self::TYPE_LABELS;
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(PlacementTestResult::class, 'placement_test_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(PlacementTestLevel::class, 'program_id', 'program_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
