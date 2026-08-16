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
 * Subscription Expired Notification
 *
 * Sent to tenant admin on the day a paid subscription expires.
 * Tenant-aware, queue-based, supports email + WhatsApp.
 */
class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use HasTenantContext;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly Subscription $subscription
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
        $tenant    = $this->subscription->tenant;
        $pkg       = $this->subscription->package_name;
        $actionUrl = $this->renewUrl();

        return (new MailMessage)
            ->subject("[{$tenant?->name}] Subscription Anda Telah Berakhir")
            ->greeting("Halo {$notifiable->name},")
            ->line("Subscription **{$pkg}** untuk pesantren **{$tenant?->name}** telah **berakhir**.")
            ->line("")
            ->line("Akses ke sistem mungkin terbatas. Segera perbarui subscription untuk memulihkan layanan penuh.")
            ->line("")
            ->line("**Detail Subscription:**")
            ->line("- Package : {$pkg}")
            ->line("- Status  : Kadaluarsa")
            ->line("")
            ->action('Perbarui Subscription Sekarang', $actionUrl)
            ->line("Hubungi kami jika membutuhkan bantuan.")
            ->line("Terima kasih telah menggunakan layanan kami.");
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        $tenant = $this->subscription->tenant;
        $pkg    = $this->subscription->package_name;

        $lines = [
            "*Assalamu'alaikum Wr. Wb.*",
            "",
            "⚠️ Subscription *{$pkg}* untuk pesantren *{$tenant?->name}* telah *BERAKHIR*.",
            "",
            "Akses sistem mungkin terbatas. Segera perbarui subscription.",
            "",
            "Perbarui di: " . $this->renewUrl(),
            "",
            "Hubungi kami jika membutuhkan bantuan.",
        ];

        return WhatsAppMessage::text(implode("\n", $lines))
            ->to($notifiable->whatsapp_number ?? $notifiable->phone ?? '')
            ->forTenant($this->subscription->tenant_id)
            ->metadata([
                'subscription_id' => $this->subscription->id,
                'type'            => 'sub_expired',
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'tenant_id'       => $this->subscription->tenant_id,
            'tenant_name'     => $this->subscription->tenant?->name,
            'package_name'    => $this->subscription->package_name,
            'ended_at'        => $this->subscription->ends_at?->toDateTimeString(),
            'type'            => 'subscription_expired',
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
