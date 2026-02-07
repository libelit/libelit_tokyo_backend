<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->confirmed()
                            ->minLength(8),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ]),
                Section::make('Role & Status')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('User Type')
                            ->options(UserTypeEnum::class)
                            ->required()
                            ->default(UserTypeEnum::DEVELOPER)
                            ->live(),
                        Select::make('status')
                            ->options(UserStatusEnum::class)
                            ->required()
                            ->default(UserStatusEnum::PENDING),
                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => in_array($get('type'), [UserTypeEnum::DEVELOPER, UserTypeEnum::LENDER])),
                    ]),
                Section::make('Avatar')
                    ->schema([
                        FileUpload::make('avatar')
                            ->image()
                            ->directory('avatars')
                            ->visibility('public')
                            ->imageEditor(),
                    ])
                    ->collapsible(),
            ]);
    }
}
