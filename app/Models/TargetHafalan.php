<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetHafalan extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'target_hafalan';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'type',
        'target',
        'deadline',
        'status',
        'catatan',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    const TYPES = ['quran', 'kitab'];

    const STATUS = ['belum', 'proses', 'tercapai'];

    const STATUS_LABELS = [
        'belum'    => 'Belum',
        'proses'   => 'Proses',
        'tercapai' => 'Tercapai',
    ];

    const STATUS_COLORS = [
        'belum'    => 'gray',
        'proses'   => 'amber',
        'tercapai' => 'emerald',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }
}
