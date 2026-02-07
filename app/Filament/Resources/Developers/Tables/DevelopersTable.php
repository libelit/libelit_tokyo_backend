<?php

namespace App\Filament\Resources\Developers\Tables;

use App\Enums\KybStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DevelopersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kyb_status')
                    ->label('KYB Status')
                    ->badge()
                    ->color(fn (KybStatusEnum $state): string => match ($state) {
                        KybStatusEnum::NOT_STARTED => 'gray',
                        KybStatusEnum::PENDING => 'warning',
                        KybStatusEnum::UNDER_REVIEW => 'info',
                        KybStatusEnum::APPROVED => 'success',
                        KybStatusEnum::REJECTED => 'danger',
                    }),
                TextColumn::make('projects_count')
                    ->label('Projects')
                    ->counts('projects')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kyb_status')
                    ->options(KybStatusEnum::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
