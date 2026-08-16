<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use App\Services\Payments\Gateways\MidtransGateway;
use App\Services\Payments\Gateways\NullPaymentGateway;
use App\Services\Payments\Gateways\XenditGateway;
use App\Services\Payments\Providers\MidtransPaymentGateway;
use App\Services\Payments\Providers\XenditPaymentGateway;
use Illuminate\Support\Facades\Log;

/**
 * Payment Gateway Factory
 *
 * Resolves the configured payment gateway driver and returns a ready-to-use
 * implementation of PaymentGatewayInterface.
 *
 * The active driver is read from config('payments.default_gateway'); it can
 * be overridden at runtime by passing a driver name to make().
 */
class PaymentGatewayFactory
{
    /** @var array<string, PaymentGatewayInterface> */
    private static array $instances = [];

    /**
     * Return a (cached) gateway instance for the given driver.
     *
     * @param  string|null $gateway Driver name. Uses config default when null.
     * @return PaymentGatewayInterface
     * @throws PaymentGatewayException
     */
    public static function make(?string $gateway = null): PaymentGatewayInterface
    {
        $gateway = $gateway ?? config('payments.default_gateway', 'null');

        if (isset(self::$instances[$gateway])) {
            return self::$instances[$gateway];
        }

        $instance = self::createGateway($gateway);
        self::$instances[$gateway] = $instance;

        return $instance;
    }

    /**
     * Create a fresh (non-cached) gateway instance.
     *
     * @param  string|null $gateway Driver name. Uses config default when null.
     * @return PaymentGatewayInterface
     * @throws PaymentGatewayException
     */
    public static function create(?string $gateway = null): PaymentGatewayInterface
    {
        $gateway = $gateway ?? config('payments.default_gateway', 'null');

        return self::createGateway($gateway);
    }

    /**
     * Clear the singleton cache (useful in tests and after config changes).
     */
    public static function clearCache(): void
    {
        self::$instances = [];
    }

    /**
     * List all registered driver names.
     *
     * @return array<string>
     */
    public static function availableGateways(): array
    {
        return array_keys(config('payments.gateways', []));
    }

    /**
     * Check whether the resolved (or default) gateway is configured and ready.
     *
     * @param  string|null $gateway Driver name. Uses config default when null.
     */
    public static function isReady(?string $gateway = null): bool
    {
        try {
            return self::make($gateway)->isConfigured();
        } catch (PaymentGatewayException $e) {
            Log::debug('Payment gateway not ready', [
                'gateway' => $gateway,
                'error'   => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Resolve and instantiate the gateway driver.
     *
     * @throws PaymentGatewayException
     */
    private static function createGateway(string $gateway): PaymentGatewayInterface
    {
        $config = config("payments.gateways.{$gateway}");

        if ($config === null) {
            throw new PaymentGatewayException(
                "Unknown payment gateway driver: {$gateway}",
                $gateway,
                'UNKNOWN_GATEWAY'
            );
        }

        return match ($gateway) {
            'midtrans' => new MidtransPaymentGateway(),
            'xendit'   => new XenditPaymentGateway(),
            'null'     => new NullPaymentGateway((array) $config),
            default    => throw new PaymentGatewayException(
                "Unsupported payment gateway driver: {$gateway}",
                $gateway,
                'UNSUPPORTED_GATEWAY'
            ),
        };
    }
}
