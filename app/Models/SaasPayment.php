<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasPayment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'transfer_proof',
        'reference_id',
        'external_id',
        'payment_url',
        'status',
        'notes',
        'confirmed_by',
        'confirmed_at',
        'paid_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'confirmed_at' => 'datetime',
        'paid_at'      => 'datetime',
    ];

    // =========================================================================
    // Constants
    // =========================================================================

    const STATUSES = ['pending', 'confirmed', 'rejected'];

    const STATUS_LABELS = [
        'pending'   => 'Menunggu Konfirmasi',
        'confirmed' => 'Dikonfirmasi',
        'rejected'  => 'Ditolak',
    ];

    const STATUS_COLORS = [
        'pending'   => 'amber',
        'confirmed' => 'emerald',
        'rejected'  => 'rose',
    ];

    const PAYMENT_METHODS = ['transfer_bank', 'va', 'qris', 'cash'];

    const PAYMENT_METHOD_LABELS = [
        'transfer_bank' => 'Transfer Bank',
        'va'            => 'Virtual Account',
        'qris'          => 'QRIS',
        'cash'          => 'Tunai',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function hasPaymentLink(): bool
    {
        return ! empty($this->payment_url);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getPaymentMethodLabel(): string
    {
        return self::PAYMENT_METHOD_LABELS[$this->payment_method] ?? $this->payment_method;
    }

    public function getFormattedAmount(): string
    {
        return 'Rp ' . number_format((float) $this->amount, 0, ',', '.');
    }
}
