<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'amount',
        'status',
        'due_date',
        'period_label',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    // =========================================================================
    // Constants
    // =========================================================================

    const STATUSES = ['unpaid', 'paid', 'failed', 'cancelled'];

    const STATUS_LABELS = [
        'unpaid'    => 'Belum Dibayar',
        'paid'      => 'Lunas',
        'failed'    => 'Gagal',
        'cancelled' => 'Dibatalkan',
    ];

    const STATUS_COLORS = [
        'unpaid'    => 'amber',
        'paid'      => 'emerald',
        'failed'    => 'rose',
        'cancelled' => 'gray',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SaasPayment::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString());
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid' && $this->due_date->isPast();
    }

    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getFormattedAmount(): string
    {
        return 'Rp ' . number_format((float) $this->amount, 0, ',', '.');
    }

    // =========================================================================
    // Static helpers
    // =========================================================================

    public static function generateNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
    }
}
