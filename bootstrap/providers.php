<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\PortalPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;
use App\Providers\PluginServiceProvider;

return [
    AppServiceProvider::class,
    PluginServiceProvider::class,   // Plugin system — loads all plugins from plugins/
    PortalPanelProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
];
