<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementTestResult extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'placement_test_results';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'placement_test_id',
        'santri_id',
        'score',
        'level_id',
        'notes',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function scopeByProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeBySantri($query, int $santriId)
    {
        return $query->where('santri_id', $santriId);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(PlacementTest::class, 'placement_test_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(PlacementTestLevel::class, 'level_id');
    }
}
