<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Jobs\Concerns\HasTenantContext;
use App\Models\Bill;
use App\Notifications\Channels\WhatsAppChannel;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillDueReminder extends Notification implements ShouldQueue
{
    use Queueable;
    use HasTenantContext;

    /** Retry twice before marking as failed */
    public int $tries = 3;

    /** Exponential backoff: 60s, 300s, 900s */
    public array $backoff = [60, 300, 900];

    public function __construct(
        private readonly Bill $bill
    ) {
        // Capture tenant context at dispatch time
        $this->setJobTenantId($bill->tenant_id);
        $this->bill->load(['santri', 'tenant']);

        // Send to reminders queue
        $this->onQueue('reminders');
    }

    public function via(object $notifiable): array
    {
        // Restore tenant context for queue processing
        $this->bootTenantContext();

        $channels = [];

        if (config('reminders.email_enabled', true) && $notifiable->email) {
            $channels[] = 'mail';
        }

        // WhatsApp channel (production-ready)
        if (config('reminders.whatsapp_enabled', false) && config('whatsapp.enabled', false)) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $santri = $this->bill->santri;
        $tenant = $this->bill->tenant;

        $typeLabel  = \App\Models\Bill::TYPE_LABELS[$this->bill->type] ?? ucfirst($this->bill->type);
        $periode    = $this->bill->due_date->translatedFormat('F Y');
        $detailUrl  = config('app.scheme') . '://'
            . ($tenant->slug ?? 'app') . '.' . config('app.app_domain')
            . '/dashboard/spp/' . $this->bill->id;

        $message = (new MailMessage)
            ->subject("[{$tenant->name}] Pengingat Pembayaran {$typeLabel}")
            ->greeting("Assalamu'alaikum Wr. Wb.")
            ->line("Anda menerima pengingat pembayaran untuk:")
            ->line("")
            ->line("**Nama Santri:** {$santri->name}")
            ->line("**NIS:** {$santri->nis}")
            ->line("**Jenis:** {$typeLabel}")
            ->line("**Periode:** {$periode}")
            ->line("**Jumlah:** Rp " . number_format((float) $this->bill->amount, 0, ',', '.'))
            ->line("**Jatuh Tempo:** {$this->bill->due_date->translatedFormat('d F Y')}")
            ->line("");

        if ($this->bill->description) {
            $message->line("**Keterangan:** {$this->bill->description}")->line("");
        }

        return $message
            ->line("Silakan melakukan pembayaran sebelum jatuh tempo.")
            ->action('Lihat Detail Tagihan', $detailUrl)
            ->line("")
            ->line("Jika sudah melakukan pembayaran, abaikan pengingat ini.")
            ->salutation("Wassalamu'alaikum Wr. Wb.,\n{$tenant->name}");
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        $santri = $this->bill->santri;
        $tenant = $this->bill->tenant;

        $typeLabel = \App\Models\Bill::TYPE_LABELS[$this->bill->type] ?? ucfirst($this->bill->type);
        $periode = $this->bill->due_date->translatedFormat('F Y');

        $lines = [
            "*Assalamu'alaikum Wr. Wb.*",
            "",
            "Anda menerima pengingat pembayaran untuk:",
            "",
            "*Nama Santri:* {$santri->name}",
            "*NIS:* {$santri->nis}",
            "*Jenis:* {$typeLabel}",
            "*Periode:* {$periode}",
            "*Jumlah:* Rp " . number_format((float) $this->bill->amount, 0, ',', '.'),
            "*Jatuh Tempo:* {$this->bill->due_date->translatedFormat('d F Y')}",
        ];

        if ($this->bill->description) {
            $lines[] = "";
            $lines[] = "*Keterangan:* {$this->bill->description}";
        }

        $lines[] = "";
        $lines[] = "Silakan melakukan pembayaran sebelum jatuh tempo.";
        $lines[] = "";
        $lines[] = "Jika sudah melakukan pembayaran, abaikan pengingat ini.";

        return WhatsAppMessage::text(implode("\n", $lines))
            ->to($notifiable->whatsapp_number ?? $notifiable->phone ?? '')
            ->forTenant($this->bill->tenant_id)
            ->metadata([
                'bill_id' => $this->bill->id,
                'type' => 'bill_reminder',
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bill_id' => $this->bill->id,
            'santri_id' => $this->bill->santri_id,
            'tenant_id' => $this->bill->tenant_id,
            'amount' => $this->bill->amount,
            'due_date' => $this->bill->due_date->toDateString(),
            'reminder_date' => now()->toDateString(),
        ];
    }
}
