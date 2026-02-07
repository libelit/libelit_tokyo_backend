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
                        TextInput::make('funding_goal')
                            ->label('Funding Goal')
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
                        TextInput::make('expected_return')
                            ->label('Expected Return (%)')
                            ->numeric()
                            ->suffix('%')
                            ->step(0.01),
                        TextInput::make('loan_term_months')
                            ->label('Loan Term (Months)')
                            ->numeric()
                            ->integer(),
                        TextInput::make('ltv_ratio')
                            ->label('LTV Ratio (%)')
                            ->numeric()
                            ->suffix('%')
                            ->step(0.01),
                    ]),

                Section::make('Risk Assessment')
                    ->columns(2)
                    ->schema([
                        Select::make('risk_score')
                            ->options([
                                1 => '1 (Low Risk)',
                                2 => '2 (Low Risk)',
                                3 => '3 (Low Risk)',
                                4 => '4 (Medium Risk)',
                                5 => '5 (Medium Risk)',
                                6 => '6 (High Risk)',
                                7 => '7 (High Risk)',
                            ])
                            ->default(4),
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
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('listed_at')
                            ->label('Listed At'),
                        DateTimePicker::make('funded_at')
                            ->label('Funded At'),
                        DateTimePicker::make('completed_at')
                            ->label('Completed At'),
                    ])
                    ->collapsible(),
            ]);
    }
}
