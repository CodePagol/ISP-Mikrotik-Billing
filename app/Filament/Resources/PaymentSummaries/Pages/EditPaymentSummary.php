<?php

namespace App\Filament\Resources\PaymentSummaries\Pages;

use App\Filament\Resources\PaymentSummaries\PaymentSummaryResource;
use Filament\Resources\Pages\EditRecord;

class EditPaymentSummary extends EditRecord
{
    protected static string $resource = PaymentSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
