<?php

/*
|--------------------------------------------------------------------------
| Plugin System Configuration
|--------------------------------------------------------------------------
|
| This file controls which plugins are loaded, where they live, and how
| premium/paid plugins are activated. The system is fully open-source
| friendly — licensing is completely optional.
|
| HOW TO ADD A PLUGIN:
|   1. Drop your plugin folder into the `plugins/` directory
|   2. Add its slug to the `enabled` array below (or use '*' for all)
|   3. Run: php artisan plugin:install {slug}
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled Plugins
    |--------------------------------------------------------------------------
    |
    | List the plugin slugs (folder names) you want to activate.
    | Use '*' to enable ALL plugins found in the plugins/ directory.
    |
    | Example:
    |   'enabled' => ['payment-gateway', 'olt-manager'],
    |
    */
    'enabled' => env('ENABLED_PLUGINS', '*') === '*'
        ? '*'
        : explode(',', env('ENABLED_PLUGINS', '')),

    /*
    |--------------------------------------------------------------------------
    | Plugin Directory
    |--------------------------------------------------------------------------
    |
    | The absolute path where plugins are stored. By default this is the
    | `plugins/` folder at the root of your Laravel project.
    |
    */
    'path' => base_path('plugins'),

    /*
    |--------------------------------------------------------------------------
    | License Keys (for Premium Plugins)
    |--------------------------------------------------------------------------
    |
    | If a plugin requires a license key, add it here.
    | The key name must match the plugin's getId() return value.
    |
    | Alternatively, set per-plugin env vars:
    |   PLUGIN_LICENSE_{SLUG_UPPERCASE}=your-key-here
    |
    | Example:
    |   'licenses' => [
    |       'olt-manager'     => env('PLUGIN_LICENSE_OLT_MANAGER'),
    |       'payment-gateway' => env('PLUGIN_LICENSE_PAYMENT_GATEWAY'),
    |   ],
    |
    */
    'licenses' => [
        // 'your-plugin-slug' => env('PLUGIN_LICENSE_YOUR_PLUGIN_SLUG'),
    ],

    /*
    |--------------------------------------------------------------------------
    | License Validation Server
    |--------------------------------------------------------------------------
    |
    | The URL of the license server used to validate premium plugin keys.
    | Plugin developers can host their own server or use a third-party service.
    | Leave null to skip remote validation (offline / honor-system mode).
    |
    */
    'license_server' => env('PLUGIN_LICENSE_SERVER', null),

    /*
    |--------------------------------------------------------------------------
    | License Grace Period (hours)
    |--------------------------------------------------------------------------
    |
    | If the license server is unreachable, plugins will continue working for
    | this many hours before re-checking. Prevents downtime from network issues.
    |
    */
    'license_grace_hours' => env('PLUGIN_LICENSE_GRACE_HOURS', 72),

    /*
    |--------------------------------------------------------------------------
    | Auto-run Migrations
    |--------------------------------------------------------------------------
    |
    | When true, plugin migrations are automatically registered and picked up
    | by `php artisan migrate`. Set to false if you prefer manual control.
    |
    */
    'auto_migrate' => env('PLUGIN_AUTO_MIGRATE', true),

];
