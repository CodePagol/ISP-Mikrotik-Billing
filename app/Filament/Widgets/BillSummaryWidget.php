<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class BillSummaryWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        if (! $user || ! $user->customer || ! $user->customer->billing) {
            return [
                Stat::make('Monthly Bill', '0.00'),
                Stat::make('Paid Amount', '0.00'),
                Stat::make('Total Due', '0.00'),
            ];
        }

        $billing = $user->customer->billing;

        return [
            Stat::make('Monthly Bill', '৳'.number_format($billing->monthly_rent, 2))
                ->description('Current Plan Rent')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 3, 5, 2, 8, 4, 10])
                ->color('info'),
            Stat::make('Paid Amount', '৳'.number_format($billing->paid_amount, 2))
                ->description('Amount paid this month')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([15, 18, 12, 24, 30, 28, 35])
                ->color('success'),
            Stat::make('Total Due', '৳'.number_format($billing->total_due_amount, 2))
                ->description($billing->total_due_amount > 0 ? 'Pending payment' : 'No outstanding balance')
                ->descriptionIcon($billing->total_due_amount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-badge')
                ->chart($billing->total_due_amount > 0 ? [10, 8, 9, 6, 5, 4, 2] : [0, 0, 0, 0, 0, 0, 0])
                ->color($billing->total_due_amount > 0 ? 'danger' : 'success'),
        ];
    }
}
