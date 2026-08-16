<?php

namespace App\Notifications;

use App\Jobs\Concerns\HasTenantContext;
use App\Models\Subscription;
use App\Services\TenantService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Trial Expiration Reminder Notification
 *
 * Sent to tenant admin before trial period expires.
 */
class TrialExpirationReminder extends Notification implements ShouldQueue
{
    use Queueable;
    use HasTenantContext;

    public function __construct(
        public Subscription $subscription,
        public int $daysUntilExpiration
    ) {
        $this->setJobTenantId($subscription->tenant_id);
    }

    public function via(object $notifiable): array
    {
        $this->bootTenantContext();
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tenant = $this->subscription->tenant;
        $packageName = $this->subscription->package_name;
        $trialEndsAt = $this->subscription->trial_ends_at;

        $message = (new MailMessage)
            ->subject("Trial akan berakhir dalam {$this->daysUntilExpiration} hari - {$tenant?->name}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Trial untuk tenant **{$tenant?->name}** akan berakhir dalam **{$this->daysUntilExpiration} hari**.")
            ->line("**Detail Trial:**")
            ->line("- Package: {$packageName}")
            ->line("- Trial berakhir: {$trialEndsAt?->translatedFormat('d F Y')}")
            ->line("- Sisa hari: {$this->daysUntilExpiration} hari");

        // Add urgency message for last day
        if ($this->daysUntilExpiration === 1) {
            $message->line('')
                ->warning('**PENTING:** Ini adalah hari terakhir trial Anda. Segera upgrade ke subscription berbayar untuk menghindari suspensi akun.');
        } elseif ($this->daysUntilExpiration <= 3) {
            $message->line('')
                ->line('**Perhatian:** Trial Anda akan segera berakhir. Jangan lupa untuk upgrade.');
        }

        $message->line('')
            ->action('Upgrade Sekarang', $this->upgradeUrl())
            ->line('Terima kasih telah menggunakan layanan kami.');

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'tenant_id' => $this->subscription->tenant_id,
            'tenant_name' => $this->subscription->tenant?->name,
            'days_until_expiration' => $this->daysUntilExpiration,
            'trial_ends_at' => $this->subscription->trial_ends_at?->toDateTimeString(),
            'package_name' => $this->subscription->package_name,
            'type' => 'trial_expiration_reminder',
        ];
    }

    private function upgradeUrl(): string
    {
        // Generate tenant-aware URL
        $tenant = $this->subscription->tenant;
        
        if (!$tenant) {
            return url('/dashboard');
        }

        // For tenant subdomain
        $host = config('app.app_domain');
        $scheme = config('app.https') ? 'https' : 'http';
        
        return "{$scheme}://{$tenant->slug}.{$host}/dashboard/subscription";
    }
}
