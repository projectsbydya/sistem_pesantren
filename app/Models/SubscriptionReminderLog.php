<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SubscriptionReminderLog extends Model
{
    use HasFactory;

    protected $table = 'subscription_reminder_logs';

    const TYPE_TRIAL_EXPIRING = 'trial_expiring';
    const TYPE_SUB_EXPIRING   = 'sub_expiring';
    const TYPE_SUB_EXPIRED    = 'sub_expired';

    protected $fillable = [
        'subscription_id',
        'tenant_id',
        'reminder_type',
        'days_before',
        'reminder_date',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'days_before'   => 'integer',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Check whether a reminder of this type/days has already been sent today.
     */
    public static function alreadySent(int $subscriptionId, string $type, int $daysBefore): bool
    {
        return static::where('subscription_id', $subscriptionId)
            ->where('reminder_type', $type)
            ->where('days_before', $daysBefore)
            ->whereDate('reminder_date', today())
            ->exists();
    }

    /**
     * Record that a reminder was sent today.
     */
    public static function record(int $subscriptionId, int $tenantId, string $type, int $daysBefore): self
    {
        return static::create([
            'subscription_id' => $subscriptionId,
            'tenant_id'       => $tenantId,
            'reminder_type'   => $type,
            'days_before'     => $daysBefore,
            'reminder_date'   => today(),
        ]);
    }
}
