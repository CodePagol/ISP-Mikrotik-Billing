<?php

namespace App\Filament\Resources\PaymentSummaries;

use App\Filament\Resources\PaymentSummaries\Pages\ListPaymentSummaries;
use App\Filament\Resources\PaymentSummaries\Schemas\PaymentSummaryForm;
use App\Filament\Resources\PaymentSummaries\Schemas\PaymentSummaryInfolist;
use App\Filament\Resources\PaymentSummaries\Tables\PaymentSummariesTable;
use App\Models\PaymentSummary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PaymentSummaryResource extends Resource
{
    protected static ?string $model = PaymentSummary::class;

    protected static ?string $navigationLabel = 'Payments Summary';

    protected static ?string $pluralLabel = 'Payments Summary';

    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'payments-summary';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('customer_payment_unique_id', Auth::user()?->customer?->customer_unique_id);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentSummaryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentSummaryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentSummariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentSummaries::route('/'),
        ];
    }
}
