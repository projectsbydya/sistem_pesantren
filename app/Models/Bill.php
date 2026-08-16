<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'bills';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'type',
        'amount',
        'paid_amount',
        'due_date',
        'status',
        'description',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    const TYPES = ['spp', 'uang_buku', 'uang_seragam'];

    const TYPE_LABELS = [
        'spp'          => 'SPP',
        'uang_buku'    => 'Uang Buku',
        'uang_seragam' => 'Uang Seragam',
    ];

    const STATUSES = ['unpaid', 'pending', 'paid'];

    const STATUS_LABELS = [
        'unpaid' => 'Belum Bayar',
        'pending' => 'Menunggu Verifikasi',
        'paid' => 'Lunas',
    ];

    const STATUS_COLORS = [
        'unpaid' => 'rose',
        'pending' => 'amber',
        'paid' => 'emerald',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function billPayments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(\App\Models\BillReminderLog::class);
    }

    public function remindersSentToday(): HasMany
    {
        return $this->hasMany(\App\Models\BillReminderLog::class)
            ->whereDate('reminder_date', today());
    }
}
