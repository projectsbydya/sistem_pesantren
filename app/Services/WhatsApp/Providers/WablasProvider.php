<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\Exceptions\WhatsAppException;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Support\Facades\Log;

/**
 * Wablas WhatsApp Provider
 *
 * Indonesian-based WhatsApp gateway provider.
 * Supports: text, media, templates
 * Docs: https://wablas.com/documentation
 */
class WablasProvider extends AbstractWhatsAppProvider
{
    protected string $providerName = 'wablas';

    protected function getRequiredConfig(): array
    {
        return ['token'];
    }

    /**
     * {@inheritDoc}
     */
    public function send(WhatsAppMessage $message): ?string
    {
        if (! $this->isConfigured()) {
            throw new WhatsAppException(
                'Wablas is not configured',
                $this->providerName,
                'NOT_CONFIGURED'
            );
        }

        $this->validateMessage($message);

        if (! $this->config['api_url']) {
            throw new WhatsAppException(
                'Wablas API URL is required',
                $this->providerName,
                'MISSING_URL'
            );
        }

        $url = rtrim($this->config['api_url'], '/');
        
        // Choose endpoint based on message type
        if ($message->hasMedia()) {
            $url .= '/api/send-image'; // or /api/send-document, /api/send-video
            $payload = $this->buildMediaPayload($message);
        } else {
            $url .= '/api/v2/send-message';
            $payload = $this->buildTextPayload($message);
        }

        $response = $this->makeRequest('post', $url, $payload);

        if (! $response->successful()) {
            $this->handleErrorResponse($response, 'Wablas API error');
        }

        $data = $response->json();

        // Check Wablas status
        if (! ($data['status'] ?? false) && ($data['status_code'] ?? 200) !== 200) {
            throw new WhatsAppException(
                $data['message'] ?? 'Wablas API error',
                $this->providerName,
                (string) ($data['status_code'] ?? 'API_ERROR'),
                ['response' => $data]
            );
        }

        $messageId = $data['data']['message_id'] ?? $data['id'] ?? null;

        $this->logSuccess($message->getTo(), $messageId, [
            'status' => $data['status'] ?? 'unknown',
        ]);

        return $messageId;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsTemplates(): bool
    {
        return true; // Wablas supports templates
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
        return false; // Limited button support
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplates(): array
    {
        // Would require fetching from Wablas API
        Log::debug('Wablas templates list - requires API integration');
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function validateNumber(string $number): bool
    {
        $sanitized = preg_replace('/[^0-9]/', '', $number);
        
        // Wablas supports international numbers
        // Must include country code
        if (strlen($sanitized) < 10 || strlen($sanitized) > 15) {
            return false;
        }

        // Should start with country code (not leading 0)
        if (str_starts_with($sanitized, '0')) {
            $sanitized = '62' . substr($sanitized, 1);
        }

        return str_starts_with($sanitized, '62') || str_starts_with($sanitized, '1');
    }

    /**
     * Build text message payload.
     */
    private function buildTextPayload(WhatsAppMessage $message): array
    {
        $to = $this->formatNumber($message->getTo());

        return [
            'phone' => $to,
            'message' => $this->buildMessageBody($message),
            'spintax' => false,
        ];
    }

    /**
     * Build media message payload.
     */
    private function buildMediaPayload(WhatsAppMessage $message): array
    {
        $to = $this->formatNumber($message->getTo());
        $mediaType = $message->getMediaType() ?? 'image';

        $payload = [
            'phone' => $to,
            'url' => $message->getMediaUrl(),
            'caption' => $message->getCaption() ?? $message->getBody() ?? '',
        ];

        // Add media type specific parameters
        if ($mediaType === 'document') {
            $payload['file_name'] = $message->getMetadata()['file_name'] ?? 'document.pdf';
        }

        return $payload;
    }

    /**
     * Build message body with footer.
     */
    private function buildMessageBody(WhatsAppMessage $message): string
    {
        $parts = [];

        if ($message->isTemplate()) {
            // Wablas template format
            $templateName = $message->getTemplate();
            $params = $message->getTemplateParams();
            $parts[] = "{{$templateName}}" . (empty($params) ? '' : '|' . implode('|', $params));
        } else {
            $parts[] = $message->getBody() ?? '';
        }

        $footer = $message->getFooter();
        if (! empty($footer)) {
            $parts[] = $footer;
        }

        return implode("\n\n", array_filter($parts));
    }

    /**
     * Format number for Wablas API.
     */
    private function formatNumber(string $number): string
    {
        // Remove non-numeric
        $number = preg_replace('/[^0-9]/', '', $number);

        // Convert leading 0 to 62
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }

    /**
     * {@inheritDoc}
     */
    protected function getHeaders(): array
    {
        return [
            'Authorization' => $this->config['token'],
            'Accept' => 'application/json',
        ];
    }
}
