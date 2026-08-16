<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaportHafalan extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'raport_hafalan';

    protected $fillable = [
        'tenant_id',
        'raport_id',
        'type',
        'surah_dari',
        'ayat_dari',
        'surah_sampai',
        'ayat_sampai',
        'juz',
        'kitab',
        'bab',
        'halaman',
        'total_hafalan',
        'keterangan',
        'predikat',
    ];

    protected $casts = [
        'ayat_dari'     => 'integer',
        'ayat_sampai'   => 'integer',
        'juz'           => 'integer',
        'halaman'       => 'integer',
        'total_hafalan' => 'integer',
    ];

    const TYPE_QURAN = 'quran';
    const TYPE_KITAB = 'kitab';

    const TYPES = ['quran', 'kitab'];

    // =========================================================================
    // Relations
    // =========================================================================

    public function raport(): BelongsTo
    {
        return $this->belongsTo(Raport::class);
    }
}
