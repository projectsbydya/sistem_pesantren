<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HafalanNilai extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'hafalan_nilai';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'kelas_id',
        'subject_id',
        'ustadz_kelas_id',
        'tanggal',
        'jenis',
        'materi',
        'nilai',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nilai'   => 'float',
    ];

    const JENIS = ['hafalan', 'nilai'];

    const JENIS_LABELS = [
        'hafalan' => 'Hafalan',
        'nilai'   => 'Nilai',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function ustadzKelas(): BelongsTo
    {
        return $this->belongsTo(UstadzKelas::class);
    }
}
