<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'kelas';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'name',
        'description',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): HasMany
    {
        return $this->hasMany(Santri::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'kelas_subject');
    }

    public function ustadzKelas(): HasMany
    {
        return $this->hasMany(UstadzKelas::class);
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByProgramSlug($query, $slug)
    {
        return $query->whereHas('program', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }
}
