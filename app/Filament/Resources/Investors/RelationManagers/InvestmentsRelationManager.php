<?php

namespace App\Filament\Resources\Investors\RelationManagers;

use App\Enums\InvestmentStatusEnum;
use App\Enums\PaymentMethodEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvestmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'investments';

    protected static ?string $title = 'Investments';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('token_quantity')
                    ->label('Tokens')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (PaymentMethodEnum $state): string => match ($state) {
                        PaymentMethodEnum::XRPL_ESCROW => 'primary',
                        PaymentMethodEnum::STABLECOIN => 'info',
                        PaymentMethodEnum::FIAT => 'warning',
                        PaymentMethodEnum::XRPL_NATIVE => 'success',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (InvestmentStatusEnum $state): string => match ($state) {
                        InvestmentStatusEnum::PENDING => 'warning',
                        InvestmentStatusEnum::CONFIRMED => 'info',
                        InvestmentStatusEnum::COMPLETED => 'success',
                        InvestmentStatusEnum::REFUNDED => 'danger',
                    }),
                TextColumn::make('confirmed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
