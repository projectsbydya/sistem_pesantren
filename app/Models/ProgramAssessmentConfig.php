<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAssessmentConfig extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'program_assessment_configs';

    protected $fillable = [
        'program_id',
        'assessment_type_id',
        'weight',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'weight'      => 'float',
        'sort_order'  => 'integer',
        'is_active'   => 'boolean',
    ];

    public function assessmentType(): BelongsTo
    {
        return $this->belongsTo(AssessmentType::class, 'assessment_type_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}
