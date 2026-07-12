<?php

namespace App\Filament\Resources\PaymentSummaries\Schemas;

use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class PaymentSummaryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ViewEntry::make('invoice')
                    ->view('filament.invoices.payment-invoice')
                    ->columnSpanFull(),
            ]);
    }
}
