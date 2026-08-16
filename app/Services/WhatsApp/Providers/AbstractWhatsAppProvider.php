<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Services\WhatsApp\Exceptions\InvalidNumberException;
use App\Services\WhatsApp\Exceptions\RateLimitException;
use App\Services\WhatsApp\Exceptions\WhatsAppException;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Abstract base class for WhatsApp providers.
 * Implements common functionality and rate limiting.
 */
abstract class AbstractWhatsAppProvider implements WhatsAppServiceInterface
{
    protected string $providerName;
    protected array $config;
    protected bool $enableRateLimit;
    protected int $rateLimitPerMinute;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->enableRateLimit = config('whatsapp.rate_limit.enabled', true);
        $this->rateLimitPerMinute = config('whatsapp.rate_limit.per_minute', 30);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function send(WhatsAppMessage $message): ?string;

    /**
     * {@inheritDoc}
     */
    public function sendBatch(array $messages): array
    {
        $results = [];
        
        foreach ($messages as $message) {
            try {
                $results[] = $this->send($message);
                
                // Rate limiting between batch items
                if ($this->enableRateLimit && count($messages) > 1) {
                    usleep((int) (60 / $this->rateLimitPerMinute * 1_000_000));
                }
            } catch (Throwable $e) {
                $results[] = null;
                Log::warning('Batch message failed', [
                    'provider' => $this->getProviderName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * {@inheritDoc}
     */
    public function isConfigured(): bool
    {
        return ! empty($this->getRequiredConfig())
            && collect($this->getRequiredConfig())
                ->every(fn ($key) => ! empty($this->config[$key] ?? null));
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        // Mask sensitive values
        return collect($this->config)
            ->map(function ($value, $key) {
                if (str_contains(strtolower($key), 'token') || str_contains(strtolower($key), 'secret')) {
                    return $value ? substr($value, 0, 4) . '****' : null;
                }
                return $value;
            })
            ->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function validateNumber(string $number): bool
    {
        // Basic validation: starts with country code, minimum length
        $sanitized = preg_replace('/[^0-9]/', '', $number);
        
        if (strlen($sanitized) < 10 || strlen($sanitized) > 15) {
            return false;
        }

        // Must start with country code (assume 62 for Indonesia as default)
        return str_starts_with($sanitized, '62') || str_starts_with($sanitized, '1');
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageStatus(string $messageId): ?array
    {
        // Default: status tracking not supported
        return null;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function supportsTemplates(): bool;

    /**
     * {@inheritDoc}
     */
    abstract public function supportsMedia(): bool;

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
     * Get required configuration keys for this provider.
     *
     * @return array<string>
     */
    abstract protected function getRequiredConfig(): array;

    /**
     * Make HTTP request with standard error handling.
     *
     * @throws WhatsAppException
     * @throws RateLimitException
     */
    protected function makeRequest(string $method, string $url, array $options = []): Response
    {
        try {
            $response = Http::withOptions([
                'timeout' => 30,
                'connect_timeout' => 10,
            ])
                ->withHeaders($this->getHeaders())
                ->$method($url, $options);

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 60);
                throw new RateLimitException(
                    'Rate limit exceeded',
                    $this->providerName,
                    $retryAfter,
                    ['url' => $url]
                );
            }

            return $response;
        } catch (RateLimitException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new WhatsAppException(
                'HTTP request failed: ' . $e->getMessage(),
                $this->providerName,
                'HTTP_ERROR',
                ['url' => $url],
                0,
                $e
            );
        }
    }

    /**
     * Get HTTP headers for API requests.
     *
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Handle API error response.
     *
     * @throws WhatsAppException
     */
    protected function handleErrorResponse(Response $response, string $context = ''): void
    {
        $errorData = $response->json() ?? [];
        $errorMessage = $errorData['message'] 
            ?? $errorData['error'] 
            ?? $errorData['error_message'] 
            ?? 'Unknown error';

        throw new WhatsAppException(
            "{$context}: {$errorMessage}",
            $this->providerName,
            (string) $response->status(),
            [
                'response' => $errorData,
                'status' => $response->status(),
            ]
        );
    }

    /**
     * Validate message before sending.
     *
     * @throws InvalidNumberException
     */
    protected function validateMessage(WhatsAppMessage $message): void
    {
        $message->validate();

        if (! $this->validateNumber($message->getTo())) {
            throw new InvalidNumberException(
                $message->getTo() ?? '',
                $this->providerName,
                ['message' => 'Invalid recipient number']
            );
        }

        if ($message->isTemplate() && ! $this->supportsTemplates()) {
            throw new WhatsAppException(
                'Templates not supported by ' . $this->providerName,
                $this->providerName,
                'TEMPLATES_UNSUPPORTED'
            );
        }

        if ($message->hasMedia() && ! $this->supportsMedia()) {
            throw new WhatsAppException(
                'Media messages not supported by ' . $this->providerName,
                $this->providerName,
                'MEDIA_UNSUPPORTED'
            );
        }
    }

    /**
     * Log successful message send.
     */
    protected function logSuccess(string $to, ?string $messageId = null, array $extra = []): void
    {
        Log::info('WhatsApp message sent', array_merge([
            'provider' => $this->providerName,
            'to' => $this->maskNumber($to),
            'message_id' => $messageId,
        ], $extra));
    }

    /**
     * Mask phone number for logging.
     */
    protected function maskNumber(string $number): string
    {
        $length = strlen($number);
        if ($length <= 8) {
            return $number;
        }
        return substr($number, 0, 4) . str_repeat('*', $length - 8) . substr($number, -4);
    }
}
