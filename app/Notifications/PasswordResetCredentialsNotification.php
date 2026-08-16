<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Jobs\Concerns\HasTenantContext;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use HasTenantContext;

    /** Retry 2 times before marking as failed */
    public int $tries = 2;

    /** Backoff: 60s, 300s */
    public array $backoff = [60, 300];

    public function __construct(
        private readonly Tenant $tenant,
        private readonly string $newPassword,
        int $tenantId
    ) {
        $this->setJobTenantId($tenantId);
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        $this->bootTenantContext();

        if (! config('notifications.password_reset_email_enabled', true)) {
            return [];
        }

        if (empty($notifiable->email)) {
            return [];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = config('app.scheme') . '://'
            . ($this->tenant->slug ?? 'app') . '.' . config('app.app_domain')
            . '/login';

        return (new MailMessage)
            ->subject("[{$this->tenant->name}] Password Anda Telah Direset")
            ->greeting("Assalamu'alaikum Wr. Wb., {$notifiable->name}!")
            ->line("Password akun Anda di **{$this->tenant->name}** telah direset oleh administrator.")
            ->line("")
            ->line("**Password Baru:** {$this->newPassword}")
            ->line("")
            ->line("**Penting:** Anda akan diminta mengganti password ini saat login berikutnya.")
            ->action('Login Sekarang', $loginUrl)
            ->line("")
            ->line("Jika Anda tidak merasa mereset password, segera hubungi administrator pesantren.")
            ->salutation("Wassalamu'alaikum Wr. Wb.,\n{$this->tenant->name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'password_reset_credentials',
            'tenant_id' => $this->tenant->id,
        ];
    }
}
