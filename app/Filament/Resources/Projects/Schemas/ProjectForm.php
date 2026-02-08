<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\KybStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Developer')
                    ->description('Only developers with approved KYB are shown')
                    ->schema([
                        Select::make('developer_id')
                            ->label('Developer')
                            ->relationship(
                                'developer',
                                'company_name',
                                fn (Builder $query) => $query->where('kyb_status', KybStatusEnum::APPROVED)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make('Project Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('project_type')
                            ->options(ProjectTypeEnum::class)
                            ->required(),
                        Select::make('status')
                            ->options(ProjectStatusEnum::class)
                            ->required()
                            ->default(ProjectStatusEnum::DRAFT),
                        RichEditor::make('description')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ]),
                    ]),

                Section::make('Location')
                    ->columns(2)
                    ->schema([
                        TextInput::make('city')
                            ->maxLength(100),
                        TextInput::make('country')
                            ->maxLength(100),
                        Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Financial Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('loan_amount')
                            ->label('Loan Amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('min_investment')
                            ->label('Minimum Investment')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                                'AED' => 'AED',
                                'SGD' => 'SGD',
                            ])
                            ->default('USD')
                            ->required(),
                    ]),

                Section::make('Approval Information')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('submitted_at')
                            ->label('Submitted At'),
                        DateTimePicker::make('approved_at')
                            ->label('Approved At'),
                        Select::make('approved_by')
                            ->label('Approved By')
                            ->relationship('approvedByUser', 'name')
                            ->searchable(),
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('status') === ProjectStatusEnum::REJECTED->value),
                    ])
                    ->collapsible()
                    ->hiddenOn('create'),

                Section::make('Timeline')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('listed_at')
                            ->label('Listed At'),
                        DateTimePicker::make('funded_at')
                            ->label('Funded At'),
                    ])
                    ->collapsible(),

                Section::make('Construction Timeline')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('construction_start_date')
                            ->label('Construction Start Date')
                            ->native(false),
                        DateTimePicker::make('construction_end_date')
                            ->label('Construction End Date')
                            ->native(false)
                            ->afterOrEqual('construction_start_date'),
                    ])
                    ->collapsible(),
            ]);
    }
}
