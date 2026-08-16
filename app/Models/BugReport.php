<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class BugReport extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'bug_reports';

    // =====================================================================
    // Constants
    // =====================================================================

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    public const CATEGORY_BUG = 'bug';
    public const CATEGORY_ERROR = 'error';
    public const CATEGORY_FEATURE_REQUEST = 'feature_request';
    public const CATEGORY_SUPPORT = 'support';

    public static array $statuses = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    public static array $severities = [
        self::SEVERITY_LOW,
        self::SEVERITY_MEDIUM,
        self::SEVERITY_HIGH,
        self::SEVERITY_CRITICAL,
    ];

    public static array $categories = [
        self::CATEGORY_BUG,
        self::CATEGORY_ERROR,
        self::CATEGORY_FEATURE_REQUEST,
        self::CATEGORY_SUPPORT,
    ];

    // =====================================================================
    // Fillable & Casts
    // =====================================================================

    protected $fillable = [
        'tenant_id',
        'reporter_id',
        'title',
        'description',
        'category',
        'status',
        'severity',
        'source_url',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // =====================================================================
    // Boot — Reporter Context
    // =====================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $bugReport) {
            // Reporter identity always comes from the authenticated context,
            // never from request input. Falls back to explicit value for seeds/tests.
            if (empty($bugReport->reporter_id) && Auth::check()) {
                $bugReport->reporter_id = Auth::id();
            }
        });

        static::deleting(function (self $bugReport) {
            // Delete attachments and their underlying files before the parent
            // record is removed so the cascade does not leave orphaned files.
            $bugReport->attachments()->get()->each->delete();
        });
    }

    // =====================================================================
    // Relations
    // =====================================================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(BugReportAttachment::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
