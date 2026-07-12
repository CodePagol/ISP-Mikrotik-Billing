<?php

namespace App\Filament\Resources\PaymentSummaries\Pages;

use App\Filament\Resources\PaymentSummaries\PaymentSummaryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentSummary extends CreateRecord
{
    protected static string $resource = PaymentSummaryResource::class;
}
