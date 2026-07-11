<?php

namespace App\Filament\Resources\PaymentSummaries\Pages;

use App\Filament\Resources\PaymentSummaries\PaymentSummaryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentSummary extends ViewRecord
{
    protected static string $resource = PaymentSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
