<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentMember extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'assignment_members';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'santri_id',
        'progress',
        'status',
        'score',
        'notes',
        'metadata',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    public function scopeByAssignment($query, int $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    public function scopeBySantri($query, int $santriId)
    {
        return $query->where('santri_id', $santriId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
