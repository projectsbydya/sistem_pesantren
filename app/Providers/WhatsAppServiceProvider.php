<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\WhatsApp\Contracts\WhatsAppServiceInterface;
use App\Services\WhatsApp\WhatsAppServiceFactory;
use Illuminate\Support\ServiceProvider;

/**
 * WhatsApp Service Provider
 *
 * Registers WhatsApp service bindings in the container.
 */
class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind WhatsApp service interface to configured provider
        $this->app->singleton(WhatsAppServiceInterface::class, function ($app) {
            return WhatsAppServiceFactory::make();
        });

        // Allow resolving by alias
        $this->app->alias(WhatsAppServiceInterface::class, 'whatsapp');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Ensure config is loaded
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(
                __DIR__ . '/../../config/whatsapp.php',
                'whatsapp'
            );
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            WhatsAppServiceInterface::class,
            'whatsapp',
        ];
    }
}
