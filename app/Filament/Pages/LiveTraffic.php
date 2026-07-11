<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LiveTraffic extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.live-traffic';

    protected static ?string $navigationLabel = 'Live Traffic';

    protected static ?string $title = 'Live Traffic Monitor';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->guard('ppp')->check();
    }
}
