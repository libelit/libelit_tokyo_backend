<?php

namespace App\Filament\Resources\Investments\Tables;

use App\Enums\InvestmentStatusEnum;
use App\Enums\PaymentMethodEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvestmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('ID')
                    ->searchable()
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->uuid),
                TextColumn::make('investor.user.name')
                    ->label('Investor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->project?->title),
                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('token_quantity')
                    ->label('Tokens')
                    ->numeric(decimalPlaces: 4)
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
                    ->label('Confirmed')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InvestmentStatusEnum::class)
                    ->multiple(),
                SelectFilter::make('payment_method')
                    ->options(PaymentMethodEnum::class),
                SelectFilter::make('project_id')
                    ->relationship('project', 'title')
                    ->label('Project')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('investor_id')
                    ->relationship('investor', 'id')
                    ->label('Investor')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? 'Investor #' . $record->id)
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
