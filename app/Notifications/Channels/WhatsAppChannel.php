<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Jobs\Concerns\HasTenantContext;
use App\Services\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * WhatsApp Notification Channel
 *
 * Sends notifications via WhatsApp using configured provider.
 * Supports queue processing with tenant context restoration.
 */
class WhatsAppChannel implements ShouldQueue
{
    use Queueable;
    use HasTenantContext;

    /** @var int Retry attempts before marking as failed */
    public int $tries;

    /** @var array<int> Backoff intervals in seconds */
    public array $backoff;

    public function __construct(
        private readonly WhatsAppServiceInterface $service,
        private readonly ?int $forcedTenantId = null
    ) {
        $this->tries = config('whatsapp.retry.tries', 3);
        $this->backoff = config('whatsapp.retry.backoff', [60, 300, 600]);
        
        if ($this->forcedTenantId) {
            $this->setJobTenantId($this->forcedTenantId);
        }

        $this->onQueue(config('whatsapp.queue.name', 'notifications'));
    }

    /**
     * Send the given notification.
     *
     * @param object $notifiable The entity receiving the notification
     * @param Notification $notification The notification instance
     * @return void
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // Restore tenant context for queue processing
        $this->bootTenantContext();

        // Check if WhatsApp is enabled globally
        if (! config('whatsapp.enabled', false)) {
            Log::debug('WhatsApp channel is disabled globally');
            return;
        }

        // Get WhatsApp number from notifiable
        $to = $this->getWhatsAppNumber($notifiable);
        
        if (empty($to)) {
            Log::info('WhatsApp notification skipped: no WhatsApp number', [
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
            ]);
            return;
        }

        // Get message from notification
        if (! method_exists($notification, 'toWhatsApp')) {
            Log::error('Notification missing toWhatsApp method', [
                'notification_class' => get_class($notification),
            ]);
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        if (! $message instanceof WhatsAppMessage) {
            Log::error('Invalid WhatsApp message returned', [
                'notification_class' => get_class($notification),
                'returned_type' => gettype($message),
            ]);
            return;
        }

        // Set recipient if not already set
        if (empty($message->getTo())) {
            $message->to($to);
        }

        // Send message with fail-safe handling
        try {
            $this->service->send($message);
            
            Log::info('WhatsApp message sent successfully', [
                'to' => $this->maskNumber($to),
                'provider' => config('whatsapp.default_provider'),
                'notification' => get_class($notification),
            ]);
        } catch (Throwable $e) {
            $this->handleSendFailure($e, $to, $notification, $message);
        }
    }

    /**
     * Get WhatsApp number from notifiable entity.
     */
    protected function getWhatsAppNumber(object $notifiable): ?string
    {
        // Priority 1: Explicit WhatsApp number attribute
        if (! empty($notifiable->whatsapp_number)) {
            return $this->sanitizeNumber($notifiable->whatsapp_number);
        }

        // Priority 2: whatsapp attribute
        if (! empty($notifiable->whatsapp)) {
            return $this->sanitizeNumber($notifiable->whatsapp);
        }

        // Priority 3: phone attribute (if configured to use as fallback)
        if (config('whatsapp.use_phone_fallback', false) && ! empty($notifiable->phone)) {
            return $this->sanitizeNumber($notifiable->phone);
        }

        // Priority 4: phone_number attribute
        if (config('whatsapp.use_phone_fallback', false) && ! empty($notifiable->phone_number)) {
            return $this->sanitizeNumber($notifiable->phone_number);
        }

        return null;
    }

    /**
     * Sanitize and format phone number for WhatsApp.
     */
    protected function sanitizeNumber(string $number): string
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Remove leading zeros if country code is present
        if (str_starts_with($number, '0') && strlen($number) > 10) {
            $number = substr($number, 1);
        }

        // Add country code if missing (assuming Indonesia)
        if (! str_starts_with($number, '62') && strlen($number) >= 10) {
            $number = '62' . $number;
        }

        return $number;
    }

    /**
     * Mask number for logging (privacy protection).
     */
    protected function maskNumber(string $number): string
    {
        $length = strlen($number);
        if ($length <= 8) {
            return $number;
        }

        return substr($number, 0, 4) . str_repeat('*', $length - 8) . substr($number, -4);
    }

    /**
     * Handle send failure with logging.
     */
    protected function handleSendFailure(
        Throwable $e,
        string $to,
        Notification $notification,
        WhatsAppMessage $message
    ): void {
        $context = [
            'error' => $e->getMessage(),
            'to' => $this->maskNumber($to),
            'provider' => config('whatsapp.default_provider'),
            'notification' => get_class($notification),
        ];

        // Include tenant info if available
        if (property_exists($this, 'tenantId')) {
            $context['tenant_id'] = $this->tenantId;
        }

        Log::error('WhatsApp message failed to send', $context);

        // Re-throw to trigger queue retry mechanism
        throw $e;
    }
}
