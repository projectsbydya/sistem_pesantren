<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionLog extends Model
{
    protected $table = 'subscription_logs';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'action',
        'old_status',
        'new_status',
        'notes',
        'performed_by',
    ];

    const ACTION_SUSPEND_TENANT  = 'suspend_tenant';
    const ACTION_ACTIVATE_TENANT = 'activate_tenant';
    const ACTION_CREATE          = 'create';
    const ACTION_CANCEL          = 'cancel';
    const ACTION_RENEW           = 'renew';
    const ACTION_ACTIVATE        = 'activate';
    const ACTION_SUSPEND         = 'suspend';
    const ACTION_EXPIRE          = 'expire';
    const ACTION_CONVERT_TRIAL   = 'convert_trial';
    const ACTION_EXTEND_GRACE    = 'extend_grace';
    const ACTION_INVOICE_CREATED = 'invoice_created';
    const ACTION_PAYMENT_CONFIRMED = 'payment_confirmed';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
