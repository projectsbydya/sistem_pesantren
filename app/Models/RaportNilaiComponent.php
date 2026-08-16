<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaportNilaiComponent extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'raport_nilai_components';

    protected $fillable = [
        'tenant_id',
        'raport_nilai_id',
        'assessment_type_id',
        'assessment_code',
        'assessment_label',
        'score',
        'weight',
        'contribution',
    ];

    protected $casts = [
        'score' => 'float',
        'weight' => 'float',
        'contribution' => 'float',
    ];

    public function raportNilai(): BelongsTo
    {
        return $this->belongsTo(RaportNilai::class);
    }

    public function assessmentType(): BelongsTo
    {
        return $this->belongsTo(AssessmentType::class);
    }
}
