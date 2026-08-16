<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BillReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'reminder_date',
    ];

    protected $casts = [
        'reminder_date' => 'date',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
