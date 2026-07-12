<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DataUsageWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        return [
            Stat::make('Bandwidth', $user->bandwidth ?? 'N/A')
                ->description('Allocated Speed')
                ->icon('heroicon-o-arrow-path'),
            Stat::make('Current Uptime', $user->uptime ?? '0s')
                ->description('Active session duration')
                ->icon('heroicon-o-clock'),
            Stat::make('Connection Status', $user->status ?? 'unknown')
                ->description('Current status')
                ->color($user->status === 'active' || $user->status === 'online' ? 'success' : 'warning')
                ->icon($user->status === 'active' || $user->status === 'online' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
        ];
    }
}
