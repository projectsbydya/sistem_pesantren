<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentType extends Model
{
    use HasFactory;

    protected $table = 'assessment_types';

    protected $fillable = [
        'code',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function programAssessmentConfigs(): HasMany
    {
        return $this->hasMany(ProgramAssessmentConfig::class, 'assessment_type_id');
    }
}
