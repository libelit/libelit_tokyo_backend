<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Enums\InvestmentStatusEnum;
use App\Enums\PaymentMethodEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvestmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'investments';

    protected static ?string $title = 'Investments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('lender_id')
                    ->label('Lender')
                    ->relationship('lender', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? $record->company_name ?? 'Lender #' . $record->id)
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                TextInput::make('token_quantity')
                    ->label('Token Quantity')
                    ->numeric()
                    ->step(0.00000001),
                Select::make('payment_method')
                    ->options(PaymentMethodEnum::class)
                    ->required(),
                TextInput::make('payment_currency')
                    ->default('USD')
                    ->maxLength(10),
                TextInput::make('payment_reference')
                    ->maxLength(255),
                Select::make('status')
                    ->options(InvestmentStatusEnum::class)
                    ->default(InvestmentStatusEnum::PENDING)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uuid')
            ->columns([
                TextColumn::make('uuid')
                    ->label('ID')
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->uuid),
                TextColumn::make('lender.user.name')
                    ->label('Lender')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('token_quantity')
                    ->label('Tokens')
                    ->numeric(decimalPlaces: 4),
                TextColumn::make('payment_method')
                    ->badge(),
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
                    ->dateTime('M d, Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InvestmentStatusEnum::class),
                SelectFilter::make('payment_method')
                    ->options(PaymentMethodEnum::class),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === InvestmentStatusEnum::PENDING)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => InvestmentStatusEnum::CONFIRMED,
                            'confirmed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Investment Confirmed')
                            ->success()
                            ->send();
                    }),
                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === InvestmentStatusEnum::CONFIRMED)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => InvestmentStatusEnum::COMPLETED,
                        ]);

                        Notification::make()
                            ->title('Investment Completed')
                            ->success()
                            ->send();
                    }),
                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, [
                        InvestmentStatusEnum::PENDING,
                        InvestmentStatusEnum::CONFIRMED,
                    ]))
                    ->requiresConfirmation()
                    ->modalHeading('Refund Investment')
                    ->modalDescription('Are you sure you want to refund this investment?')
                    ->action(function ($record) {
                        $record->update([
                            'status' => InvestmentStatusEnum::REFUNDED,
                        ]);

                        Notification::make()
                            ->title('Investment Refunded')
                            ->warning()
                            ->send();
                    }),
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
