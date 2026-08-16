<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Jobs\Concerns\HasTenantContext;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use HasTenantContext;

    /** Retry 2 times before marking as failed */
    public int $tries = 2;

    /** Backoff: 60s, 300s */
    public array $backoff = [60, 300];

    public function __construct(
        private readonly Tenant $tenant,
        private readonly string $loginEmail,
        private readonly string $plainPassword,
        private readonly string $roleLabel,
        int $tenantId
    ) {
        $this->setJobTenantId($tenantId);
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        $this->bootTenantContext();

        if (! config('notifications.welcome_email_enabled', true)) {
            return [];
        }

        if (empty($notifiable->email)) {
            return [];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = config('app.scheme') . '://' . config('app.app_domain') . '/login';

        return (new MailMessage)
            ->subject("[{$this->tenant->name}] Selamat Datang - Akun Anda Telah Dibuat")
            ->greeting("Assalamu'alaikum Wr. Wb., {$notifiable->name}!")
            ->line("Akun Anda sebagai **{$this->roleLabel}** di **{$this->tenant->name}** telah berhasil dibuat.")
            ->line("")
            ->line("**Informasi Login:**")
            ->line("**Email:** {$this->loginEmail}")
            ->line("**Password:** {$this->plainPassword}")
            ->line("")
            ->line("**Penting:** Anda akan diminta mengganti password saat pertama kali login.")
            ->action('Login Sekarang', $loginUrl)
            ->line("")
            ->line("Harap simpan informasi ini dan jangan bagikan kepada siapapun.")
            ->salutation("Wassalamu'alaikum Wr. Wb.,\n{$this->tenant->name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'welcome',
            'tenant_id'  => $this->tenant->id,
            'role_label' => $this->roleLabel,
        ];
    }
}
