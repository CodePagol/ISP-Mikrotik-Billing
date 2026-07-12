<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\PortalPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;
return [
    AppServiceProvider::class,
    PortalPanelProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
];
