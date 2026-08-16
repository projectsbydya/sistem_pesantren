<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Services\WhatsApp\Exceptions\WhatsAppException;
use App\Services\WhatsApp\Providers\FonnteProvider;
use App\Services\WhatsApp\Providers\TwilioProvider;
use App\Services\WhatsApp\Providers\WablasProvider;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Service Factory
 *
 * Factory for creating configured WhatsApp provider instances.
 * Supports runtime provider switching based on configuration.
 */
class WhatsAppServiceFactory
{
    /** @var array<string, WhatsAppServiceInterface> */
    private static array $instances = [];

    /**
     * Create a WhatsApp service instance.
     *
     * @param string|null $provider Provider name (twilio, fonnte, wablas). Uses config default if null.
     * @return WhatsAppServiceInterface
     * @throws WhatsAppException
     */
    public static function make(?string $provider = null): WhatsAppServiceInterface
    {
        $provider = $provider ?? config('whatsapp.default_provider', 'twilio');

        if (isset(self::$instances[$provider])) {
            return self::$instances[$provider];
        }

        $instance = self::createProvider($provider);
        self::$instances[$provider] = $instance;

        return $instance;
    }

    /**
     * Create a fresh instance (bypass singleton cache).
     *
     * @param string|null $provider Provider name
     * @return WhatsAppServiceInterface
     * @throws WhatsAppException
     */
    public static function create(?string $provider = null): WhatsAppServiceInterface
    {
        $provider = $provider ?? config('whatsapp.default_provider', 'twilio');
        return self::createProvider($provider);
    }

    /**
     * Clear cached instances.
     */
    public static function clearCache(): void
    {
        self::$instances = [];
    }

    /**
     * Get available providers.
     *
     * @return array<string>
     */
    public static function availableProviders(): array
    {
        return ['twilio', 'fonnte', 'wablas'];
    }

    /**
     * Check if provider is configured and ready.
     *
     * @param string|null $provider Provider name
     * @return bool
     */
    public static function isReady(?string $provider = null): bool
    {
        try {
            $service = self::make($provider);
            return $service->isConfigured();
        } catch (WhatsAppException $e) {
            Log::debug('WhatsApp provider not ready', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Create provider instance.
     *
     * @throws WhatsAppException
     */
    private static function createProvider(string $provider): WhatsAppServiceInterface
    {
        $config = config("whatsapp.providers.{$provider}", []);

        if (empty($config)) {
            throw new WhatsAppException(
                "Unknown WhatsApp provider: {$provider}",
                $provider,
                'UNKNOWN_PROVIDER'
            );
        }

        return match ($provider) {
            'twilio' => new TwilioProvider($config),
            'fonnte' => new FonnteProvider($config),
            'wablas' => new WablasProvider($config),
            default => throw new WhatsAppException(
                "Unsupported WhatsApp provider: {$provider}",
                $provider,
                'UNSUPPORTED_PROVIDER'
            ),
        };
    }
}
