<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\Exceptions\WhatsAppException;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Support\Facades\Log;

/**
 * Twilio WhatsApp Provider
 *
 * Supports: text, media, templates (Twilio Content API)
 * Docs: https://www.twilio.com/docs/whatsapp/api
 */
class TwilioProvider extends AbstractWhatsAppProvider
{
    protected string $providerName = 'twilio';

    protected function getRequiredConfig(): array
    {
        return ['sid', 'auth_token', 'from'];
    }

    /**
     * {@inheritDoc}
     */
    public function send(WhatsAppMessage $message): ?string
    {
        if (! $this->isConfigured()) {
            throw new WhatsAppException(
                'Twilio is not configured',
                $this->providerName,
                'NOT_CONFIGURED'
            );
        }

        $this->validateMessage($message);

        $url = sprintf(
            '%s/Accounts/%s/Messages.json',
            rtrim($this->config['api_url'], '/'),
            $this->config['sid']
        );

        $payload = $this->buildPayload($message);

        $response = $this->makeRequest('asForm', $url, $payload);

        if (! $response->successful()) {
            $this->handleErrorResponse($response, 'Twilio API error');
        }

        $data = $response->json();
        $messageId = $data['sid'] ?? null;

        $this->logSuccess($message->getTo(), $messageId, [
            'status' => $data['status'] ?? 'unknown',
        ]);

        return $messageId;
    }

    /**
     * {@inheritDoc}
     */
    public function sendBatch(array $messages): array
    {
        // Twilio supports batching but we'll use sequential for now
        return parent::sendBatch($messages);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsTemplates(): bool
    {
        return true; // Via Twilio Content API
    }

    /**
     * {@inheritDoc}
     */
    public function supportsMedia(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsButtons(): bool
    {
        return true; // Via Twilio Content API templates
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageStatus(string $messageId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $url = sprintf(
            '%s/Accounts/%s/Messages/%s.json',
            rtrim($this->config['api_url'], '/'),
            $this->config['sid'],
            $messageId
        );

        $response = $this->makeRequest('get', $url);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return [
            'id' => $data['sid'] ?? $messageId,
            'status' => $data['status'] ?? 'unknown',
            'error_code' => $data['error_code'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'date_sent' => $data['date_sent'] ?? null,
            'date_delivered' => $data['date_delivered'] ?? null,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplates(): array
    {
        // Requires Twilio Content API integration
        Log::debug('Twilio templates list - requires Content API setup');
        return [];
    }

    /**
     * Build Twilio API payload.
     */
    private function buildPayload(WhatsAppMessage $message): array
    {
        $from = $message->getFrom() ?? $this->config['from'];
        
        // Ensure WhatsApp prefix for from number
        if (! str_starts_with($from, 'whatsapp:')) {
            $from = 'whatsapp:' . $from;
        }

        $to = $message->getTo();
        if (! str_starts_with($to, 'whatsapp:')) {
            $to = 'whatsapp:' . $to;
        }

        $payload = [
            'From' => $from,
            'To' => $to,
        ];

        // Template or regular message
        if ($message->isTemplate() && $this->supportsTemplates()) {
            $payload['ContentSid'] = $message->getTemplate();
            $payload['ContentVariables'] = json_encode($message->getTemplateParams());
        } elseif ($message->hasMedia()) {
            $payload['MediaUrl'] = $message->getMediaUrl();
            if ($message->getCaption()) {
                $payload['Body'] = $message->getCaption();
            }
        } else {
            $payload['Body'] = $this->buildMessageBody($message);
        }

        return $payload;
    }

    /**
     * Build message body with optional footer.
     */
    private function buildMessageBody(WhatsAppMessage $message): string
    {
        $body = $message->getBody() ?? '';
        
        $footer = $message->getFooter();
        if (! empty($footer)) {
            $body .= "\n\n" . $footer;
        }

        return $body;
    }

    /**
     * {@inheritDoc}
     */
    protected function getHeaders(): array
    {
        $auth = base64_encode($this->config['sid'] . ':' . $this->config['auth_token']);
        
        return [
            'Authorization' => 'Basic ' . $auth,
            'Accept' => 'application/json',
        ];
    }
}
