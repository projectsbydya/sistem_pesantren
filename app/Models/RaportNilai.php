<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RaportNilai extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'raport_nilai';

    protected $fillable = [
        'tenant_id',
        'raport_id',
        'subject_id',
        'nilai_harian',
        'nilai_uts',
        'nilai_uas',
        'nilai_praktik',
        'nilai_akhir',
        'predikat',
        'deskripsi',
    ];

    protected $casts = [
        'nilai_harian' => 'float',
        'nilai_uts'    => 'float',
        'nilai_uas'    => 'float',
        'nilai_praktik'=> 'float',
        'nilai_akhir'  => 'float',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function nilaiComponents(): HasMany
    {
        return $this->hasMany(RaportNilaiComponent::class);
    }

    public function raport(): BelongsTo
    {
        return $this->belongsTo(Raport::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

}
