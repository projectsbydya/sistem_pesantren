<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Notifications\Notification;

/**
 * In-app notification sent to the related Santri, Wali, and Orang Tua
 * when a new SPP bill is created.
 *
 * Uses only the database channel so the notification appears in the
 * notification bell UI. Delivered synchronously to avoid depending on a
 * queue worker for immediate feedback.
 */
class SppBillCreatedNotification extends Notification
{
    public function __construct(public readonly Bill $bill)
    {
    }

    /**
     * Deliver only via the in-app database channel.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Build the database payload for the in-app notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $bill = $this->bill->fresh(['santri']);
        $santri = $bill->santri;

        $typeLabel = Bill::TYPE_LABELS[$bill->type] ?? ucfirst($bill->type);
        $amount = 'Rp ' . number_format((float) $bill->amount, 0, ',', '.');
        $dueDate = $bill->due_date?->format('d M Y') ?? '-';
        $santriName = $santri?->name ?? 'Santri';

        return [
            'type'       => 'spp_bill_created',
            'title'      => "[Tagihan Baru] {$typeLabel}",
            'message'    => "Tagihan {$typeLabel} sebesar {$amount} untuk {$santriName} telah dibuat. Jatuh tempo: {$dueDate}.",
            'action_url' => tenant_route('dashboard.spp.show', $bill->id, true),
            'bill_id'    => $bill->id,
            'santri_id'  => $bill->santri_id,
            'tenant_id'  => $bill->tenant_id,
            'bill_type'  => $bill->type,
            'amount'     => $bill->amount,
            'due_date'   => $bill->due_date?->toDateString(),
            'status'     => $bill->status,
            'created_at' => $bill->created_at?->toDateTimeString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'spp_bill_created',
            'bill_id'   => $this->bill->id,
            'santri_id' => $this->bill->santri_id,
            'tenant_id' => $this->bill->tenant_id,
            'bill_type' => $this->bill->type,
            'amount'    => $this->bill->amount,
        ];
    }
}
