<?php

namespace App\Filament\Resources\Developers\RelationManagers;

use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    protected static ?string $title = 'Projects';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project_type')
                    ->badge()
                    ->color(fn (ProjectTypeEnum $state): string => match ($state) {
                        ProjectTypeEnum::RESIDENTIAL => 'info',
                        ProjectTypeEnum::COMMERCIAL => 'warning',
                        ProjectTypeEnum::MIXED_USE => 'primary',
                        ProjectTypeEnum::INDUSTRIAL => 'gray',
                        ProjectTypeEnum::LAND => 'success',
                    }),
                TextColumn::make('funding_goal')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ProjectStatusEnum $state): string => match ($state) {
                        ProjectStatusEnum::DRAFT => 'gray',
                        ProjectStatusEnum::SUBMITTED => 'info',
                        ProjectStatusEnum::UNDER_REVIEW => 'warning',
                        ProjectStatusEnum::APPROVED => 'success',
                        ProjectStatusEnum::REJECTED => 'danger',
                        ProjectStatusEnum::FUNDED => 'primary',
                        ProjectStatusEnum::COMPLETED => 'success',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.projects.view', $record)),
            ]);
    }
}
