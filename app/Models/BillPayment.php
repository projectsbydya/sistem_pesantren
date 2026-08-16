<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BillPayment extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'bill_id',
        'santri_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'transfer_proof',
        'notes',
        'submitted_at',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    public const STATUS_LABELS = [
        'pending' => 'Menunggu Verifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    public const STATUS_COLORS = [
        'pending' => 'amber',
        'approved' => 'emerald',
        'rejected' => 'rose',
    ];

    public const METHODS = ['cash', 'transfer', 'qris', 'ewallet'];

    public const METHOD_LABELS = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'ewallet' => 'E-Wallet',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isDigital(): bool
    {
        return in_array($this->payment_method, ['transfer', 'qris', 'ewallet'], true);
    }

    public function isManual(): bool
    {
        return $this->payment_method === 'cash';
    }
}
