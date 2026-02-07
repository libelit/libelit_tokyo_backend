<?php

namespace App\Filament\Resources\Investments\Schemas;

use App\Enums\InvestmentStatusEnum;
use App\Enums\PaymentMethodEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvestmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Investment Details')
                    ->columns(2)
                    ->schema([
                        Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('lender_id')
                            ->label('Lender')
                            ->relationship('lender', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? $record->company_name ?? 'Lender #' . $record->id)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('token_id')
                            ->label('Token')
                            ->relationship('token', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Amount & Tokens')
                    ->columns(2)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Investment Amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('token_quantity')
                            ->label('Token Quantity')
                            ->numeric()
                            ->step(0.00000001),
                    ]),

                Section::make('Payment Information')
                    ->columns(2)
                    ->schema([
                        Select::make('payment_method')
                            ->options(PaymentMethodEnum::class)
                            ->required(),
                        TextInput::make('payment_currency')
                            ->default('USD')
                            ->maxLength(10),
                        TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->maxLength(255),
                        TextInput::make('xrpl_tx_hash')
                            ->label('XRPL Transaction Hash')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options(InvestmentStatusEnum::class)
                            ->required()
                            ->default(InvestmentStatusEnum::PENDING),
                        DateTimePicker::make('confirmed_at')
                            ->label('Confirmed At'),
                    ]),
            ]);
    }
}
