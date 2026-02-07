<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->title),
                TextColumn::make('developer.company_name')
                    ->label('Developer')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('project_type')
                    ->badge()
                    ->color(fn (ProjectTypeEnum $state): string => match ($state) {
                        ProjectTypeEnum::RESIDENTIAL => 'info',
                        ProjectTypeEnum::COMMERCIAL => 'warning',
                        ProjectTypeEnum::MIXED_USE => 'success',
                        ProjectTypeEnum::INDUSTRIAL => 'gray',
                        ProjectTypeEnum::LAND => 'primary',
                    }),
                TextColumn::make('funding_goal')
                    ->label('Funding Goal')
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ProjectStatusEnum $state): string => match ($state) {
                        ProjectStatusEnum::DRAFT => 'gray',
                        ProjectStatusEnum::SUBMITTED => 'info',
                        ProjectStatusEnum::UNDER_REVIEW => 'warning',
                        ProjectStatusEnum::APPROVED => 'success',
                        ProjectStatusEnum::REJECTED => 'danger',
                        ProjectStatusEnum::FUNDING => 'warning',
                        ProjectStatusEnum::FUNDED => 'primary',
                        ProjectStatusEnum::COMPLETED => 'success',
                    }),
                TextColumn::make('risk_score')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 1 && $state <= 3 => 'success',
                        $state >= 4 && $state <= 5 => 'warning',
                        $state >= 6 && $state <= 7 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProjectStatusEnum::class)
                    ->multiple(),
                SelectFilter::make('project_type')
                    ->options(ProjectTypeEnum::class)
                    ->multiple(),
                SelectFilter::make('risk_score')
                    ->options([
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        6 => '6',
                        7 => '7',
                    ]),
                SelectFilter::make('developer_id')
                    ->relationship('developer', 'company_name')
                    ->label('Developer')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
