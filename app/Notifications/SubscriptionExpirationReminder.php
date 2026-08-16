<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Jobs\Concerns\HasTenantContext;
use App\Models\Subscription;
use App\Notifications\Channels\WhatsAppChannel;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Subscription Expiration Reminder
 *
 * Sent to tenant admin N days before a paid subscription expires.
 * Tenant-aware, queue-based, supports email + WhatsApp.
 */
class SubscriptionExpirationReminder extends Notification implements ShouldQueue
{
    use Queueable;
    use HasTenantContext;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly Subscription $subscription,
        public readonly int $daysUntilExpiration
    ) {
        $this->setJobTenantId($subscription->tenant_id);
        $this->onQueue(config('reminders.queue_name', 'reminders'));
    }

    public function via(object $notifiable): array
    {
        $this->bootTenantContext();

        $channels = ['database'];

        if (config('reminders.subscription_email_enabled', true) && ! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        if (config('reminders.whatsapp_enabled', false) && config('whatsapp.enabled', false)) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tenant     = $this->subscription->tenant;
        $endsAt     = $this->subscription->ends_at;
        $pkg        = $this->subscription->package_name;
        $days       = $this->daysUntilExpiration;
        $actionUrl  = $this->renewUrl();

        $message = (new MailMessage)
            ->subject("[{$tenant?->name}] Subscription akan berakhir dalam {$days} hari")
            ->greeting("Halo {$notifiable->name},")
            ->line("Subscription **{$pkg}** untuk pesantren **{$tenant?->name}** akan berakhir dalam **{$days} hari**.")
            ->line("")
            ->line("**Detail Subscription:**")
            ->line("- Package : {$pkg}")
            ->line("- Berakhir: {$endsAt?->translatedFormat('d F Y')}")
            ->line("- Sisa    : {$days} hari");

        if ($days === 1) {
            $message->line("")
                ->line("**PENTING:** Ini adalah hari terakhir. Segera perbarui subscription untuk menghindari gangguan layanan.");
        } elseif ($days <= 3) {
            $message->line("")
                ->line("**Perhatian:** Subscription akan segera berakhir. Jangan lupa memperbarui.");
        }

        $message->line("")
            ->action('Perbarui Subscription', $actionUrl)
            ->line("Terima kasih telah menggunakan layanan kami.");

        return $message;
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        $tenant  = $this->subscription->tenant;
        $endsAt  = $this->subscription->ends_at;
        $pkg     = $this->subscription->package_name;
        $days    = $this->daysUntilExpiration;

        $lines = [
            "*Assalamu'alaikum Wr. Wb.*",
            "",
            "Subscription *{$pkg}* untuk pesantren *{$tenant?->name}* akan berakhir dalam *{$days} hari*.",
            "",
            "*Detail Subscription:*",
            "- Package : {$pkg}",
            "- Berakhir: {$endsAt?->translatedFormat('d F Y')}",
            "- Sisa    : {$days} hari",
        ];

        if ($days <= 3) {
            $lines[] = "";
            $lines[] = $days === 1
                ? "*PENTING:* Ini hari terakhir! Segera perbarui subscription."
                : "*Perhatian:* Subscription akan segera berakhir.";
        }

        $lines[] = "";
        $lines[] = "Perbarui di: " . $this->renewUrl();

        return WhatsAppMessage::text(implode("\n", $lines))
            ->to($notifiable->whatsapp_number ?? $notifiable->phone ?? '')
            ->forTenant($this->subscription->tenant_id)
            ->metadata([
                'subscription_id' => $this->subscription->id,
                'type'            => 'sub_expiring',
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'subscription_id'    => $this->subscription->id,
            'tenant_id'          => $this->subscription->tenant_id,
            'tenant_name'        => $this->subscription->tenant?->name,
            'package_name'       => $this->subscription->package_name,
            'days_until_expiry'  => $this->daysUntilExpiration,
            'ends_at'            => $this->subscription->ends_at?->toDateTimeString(),
            'type'               => 'subscription_expiration_reminder',
        ];
    }

    private function renewUrl(): string
    {
        $tenant = $this->subscription->tenant;

        if (! $tenant) {
            return url('/dashboard');
        }

        $scheme = config('app.https') ? 'https' : 'http';
        $host   = config('app.app_domain');

        return "{$scheme}://{$tenant->slug}.{$host}/dashboard/subscription";
    }
}
