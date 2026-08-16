<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | The driver that will be used when no explicit gateway is requested.
    | Supported: "midtrans", "xendit", "null"
    |
    */

    'default_gateway' => env('PAYMENT_GATEWAY', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Gateway Configurations
    |--------------------------------------------------------------------------
    |
    | Each gateway has its own configuration block.  Add additional gateways
    | here and register them in PaymentGatewayFactory::createGateway().
    |
    */

    'gateways' => [

        'midtrans' => [
            'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
            'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        ],

        'xendit' => [
            'secret_key'    => env('XENDIT_SECRET_KEY', ''),
            'webhook_token' => env('XENDIT_WEBHOOK_TOKEN', ''),
            'is_production' => env('XENDIT_IS_PRODUCTION', false),
        ],

        'null' => [],

    ],

];
