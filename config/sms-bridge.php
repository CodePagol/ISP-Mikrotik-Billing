<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS gateway connection that gets used
    | while using this package. You can specify any of the supported gateways
    | like 'adnsms', 'elitbuzz', 'alphasms' etc.
    |
    */

    'default' => env('SMS_BRIDGE_GATEWAY', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Unified Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Here we define the single unified credentials for the active gateway.
    | The SmsBridgeManager will pass these credentials to whatever driver
    | is selected in 'default'. This prevents you from needing separate
    | .env variables for every single provider.
    |
    */

    'credentials' => [
        'url' => env('SMS_BRIDGE_URL', ''),
        'api_send_url' => env('SMS_BRIDGE_SEND_URL', ''),
        'api_balance_url' => env('SMS_BRIDGE_BALANCE_URL', ''),
        'api_profile_url' => env('SMS_BRIDGE_PROFILE_URL', ''),
        'api_key' => env('SMS_BRIDGE_API_KEY', ''),
        'api_secret' => env('SMS_BRIDGE_API_SECRET', ''),
        'sender_id' => env('SMS_BRIDGE_SENDER_ID', ''),
    ],
];
