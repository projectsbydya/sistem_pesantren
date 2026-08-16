<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tabungan extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'tabungan';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'jenis',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah'  => 'decimal:2',
    ];

    const JENIS = ['setor', 'tarik'];

    const JENIS_LABELS = [
        'setor' => 'Setoran',
        'tarik' => 'Penarikan',
    ];

    const JENIS_COLORS = [
        'setor' => 'emerald',
        'tarik' => 'rose',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeSetor($query)
    {
        return $query->where('jenis', 'setor');
    }

    public function scopeTarik($query)
    {
        return $query->where('jenis', 'tarik');
    }
}
