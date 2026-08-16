<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiKamar extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'mutasi_kamar';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'kamar_asal_id',
        'kamar_tujuan_id',
        'tanggal_mutasi',
        'alasan',
        'keterangan',
        'processed_by',
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function kamarAsal(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_asal_id');
    }

    public function kamarTujuan(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_tujuan_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('tanggal_mutasi', [$from, $to]);
    }
}
