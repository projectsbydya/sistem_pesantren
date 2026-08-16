<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlacementTestLevel extends Model
{
    use HasTenant;

    protected $table = 'placement_test_levels';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'min_score',
        'max_score',
        'label',
        'is_active',
    ];

    protected $casts = [
        'min_score' => 'integer',
        'max_score' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
        return $this->hasMany(PlacementTestResult::class, 'level_id');
    }
}
