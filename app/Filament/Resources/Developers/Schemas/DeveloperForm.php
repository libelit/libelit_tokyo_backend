<?php

namespace App\Filament\Resources\Developers\Schemas;

use App\Enums\KybStatusEnum;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeveloperForm
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

                Section::make('Company Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('company_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('company_registration_number')
                            ->maxLength(100),
                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('KYB Verification')
                    ->columns(2)
                    ->schema([
                        Select::make('kyb_status')
                            ->options(KybStatusEnum::class)
                            ->required()
                            ->default(KybStatusEnum::NOT_STARTED)
                            ->live(),
                        Select::make('kyb_approved_by')
                            ->label('Approved By')
                            ->options(User::where('type', 'admin')->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn ($get) => $get('kyb_status') === KybStatusEnum::APPROVED->value),
                        DateTimePicker::make('kyb_submitted_at')
                            ->label('Submitted At'),
                        DateTimePicker::make('kyb_approved_at')
                            ->label('Approved At')
                            ->visible(fn ($get) => $get('kyb_status') === KybStatusEnum::APPROVED->value),
                        Textarea::make('kyb_rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('kyb_status') === KybStatusEnum::REJECTED->value),
                    ]),
            ]);
    }
}
