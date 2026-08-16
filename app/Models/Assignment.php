<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'assignments';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'kelas_id',
        'type',
        'title',
        'target',
        'state',
        'published_at',
        'due_date',
        'notes',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'due_date'     => 'date',
        'metadata'     => 'array',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(AssignmentMember::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    public function scopeByProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByKelas($query, ?int $kelasId)
    {
        if ($kelasId === null) {
            return $query;
        }

        return $query->where('kelas_id', $kelasId);
    }

    public function scopePublished($query)
    {
        return $query->where('state', 'published');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    public function isPublished(): bool
    {
        return $this->state === 'published';
    }

    public function isDraft(): bool
    {
        return $this->state === 'draft';
    }
}
