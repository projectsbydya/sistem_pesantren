<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\Exceptions\WhatsAppException;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Support\Facades\Log;

/**
 * Fonnte WhatsApp Provider
 *
 * Indonesian-based WhatsApp API provider.
 * Supports: text, media
 * Docs: https://fonnte.com/documentation
 */
class FonnteProvider extends AbstractWhatsAppProvider
{
    protected string $providerName = 'fonnte';

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
                'Fonnte is not configured',
                $this->providerName,
                'NOT_CONFIGURED'
            );
        }

        $this->validateMessage($message);

        $url = $this->config['api_url'] ?? 'https://api.fonnte.com/send';
        $payload = $this->buildPayload($message);

        $response = $this->makeRequest('post', $url, $payload);

        if (! $response->successful()) {
            $this->handleErrorResponse($response, 'Fonnte API error');
        }

        $data = $response->json();

        if (! ($data['status'] ?? false)) {
            throw new WhatsAppException(
                $data['reason'] ?? 'Fonnte API error',
                $this->providerName,
                $data['status_code'] ?? 'API_ERROR',
                ['response' => $data]
            );
        }

        $this->logSuccess($message->getTo(), null, [
            'response' => $data,
        ]);

        // Fonnte doesn't return message ID in free tier
        return $data['id'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsTemplates(): bool
    {
        return false; // Fonnte doesn't support official WhatsApp templates
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
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplates(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function validateNumber(string $number): bool
    {
        $sanitized = preg_replace('/[^0-9]/', '', $number);
        
        // Fonnte primarily supports Indonesian numbers
        if (! str_starts_with($sanitized, '62') && ! str_starts_with($sanitized, '0')) {
            return false;
        }

        // Remove leading 0 for validation
        if (str_starts_with($sanitized, '0')) {
            $sanitized = '62' . substr($sanitized, 1);
        }

        // Indonesian numbers: 62 + 10-13 digits
        $length = strlen($sanitized);
        return $length >= 11 && $length <= 15;
    }

    /**
     * Build Fonnte API payload.
     */
    private function buildPayload(WhatsAppMessage $message): array
    {
        $countryCode = $this->config['country_code'] ?? '62';
        $to = $message->getTo();

        // Ensure proper country code
        if (str_starts_with($to, '0')) {
            $to = $countryCode . substr($to, 1);
        }

        $payload = [
            'target' => $to,
            'message' => $this->buildMessageBody($message),
        ];

        // Add media if present
        if ($message->hasMedia()) {
            $payload['url'] = $message->getMediaUrl();
        }

        return $payload;
    }

    /**
     * Build message body with footer.
     */
    private function buildMessageBody(WhatsAppMessage $message): string
    {
        $parts = [$message->getBody() ?? ''];

        $footer = $message->getFooter();
        if (! empty($footer)) {
            $parts[] = $footer;
        }

        return implode("\n\n", array_filter($parts));
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
