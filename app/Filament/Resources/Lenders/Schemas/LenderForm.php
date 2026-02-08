<?php

namespace App\Filament\Resources\Lenders\Schemas;

use App\Enums\LenderTypeEnum;
use App\Enums\KycStatusEnum;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Account')
                    ->description('Link to user account')
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('email')->email()->required(),
                                TextInput::make('password')->password()->required(),
                            ]),
                    ]),

                Section::make('Lender Information')
                    ->columns(2)
                    ->schema([
                        Select::make('lender_type')
                            ->options(LenderTypeEnum::class)
                            ->required()
                            ->default(LenderTypeEnum::TIER_1),
                        TextInput::make('company_name')
                            ->maxLength(255),
                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('KYC Verification')
                    ->columns(2)
                    ->schema([
                        Select::make('kyc_status')
                            ->options(KycStatusEnum::class)
                            ->required()
                            ->default(KycStatusEnum::NOT_STARTED)
                            ->live(),
                        Select::make('kyc_approved_by')
                            ->label('Approved By')
                            ->options(User::where('type', 'admin')->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn ($get) => $get('kyc_status') === KycStatusEnum::APPROVED->value),
                        DateTimePicker::make('kyc_submitted_at')
                            ->label('Submitted At'),
                        DateTimePicker::make('kyc_approved_at')
                            ->label('Approved At')
                            ->visible(fn ($get) => $get('kyc_status') === KycStatusEnum::APPROVED->value),
                        Textarea::make('kyc_rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('kyc_status') === KycStatusEnum::REJECTED->value),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Allow this lender to make investments'),
                    ]),
            ]);
    }
}
