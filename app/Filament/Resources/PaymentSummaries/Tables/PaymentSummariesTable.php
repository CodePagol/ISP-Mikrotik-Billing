<?php

namespace App\Filament\Resources\PaymentSummaries\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentSummariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('summary_date', 'desc')
            ->columns([
                TextColumn::make('customer_payment_unique_id')
                    ->label('Customer ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('id')
                    ->label('Invoice ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('summary_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('monthly_rent')
                    ->label('Monthly Rent')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency'))
                    ->sortable(),
                TextColumn::make('additional_charge')
                    ->label('Extra')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency')),
                TextColumn::make('discount')
                    ->label('Discount')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency')),
                TextColumn::make('advance')
                    ->label('Advance')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency')),
                TextColumn::make('vat')
                    ->label('VAT')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency')),
                TextColumn::make('previous_due')
                    ->label('Prev. Due')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency')),
                TextColumn::make('total')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency')),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency')),
                TextColumn::make('due_amount')
                    ->label('Due')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' '.siteUrlSettings('site_currency')),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
