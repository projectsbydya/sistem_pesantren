<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'subjects';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'name',
        'code',
        'description',
    ];

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_subject');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function ustadz(): BelongsToMany
    {
        return $this->belongsToMany(Ustadz::class, 'subject_ustadz')
            ->withTimestamps();
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
