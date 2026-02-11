<?php

namespace App\Filament\Resources\Lenders\Tables;

use App\Enums\LenderTypeEnum;
use App\Enums\KybStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LendersTable
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
                TextColumn::make('lender_type')
                    ->badge()
                    ->color(fn (LenderTypeEnum $state): string => match ($state) {
                        LenderTypeEnum::TIER_1 => 'success',
                        LenderTypeEnum::TIER_2 => 'info',
                        LenderTypeEnum::TIER_3 => 'gray',
                    }),
                TextColumn::make('company_name')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('kyb_status')
                    ->label('KYB')
                    ->badge()
                    ->color(fn (KybStatusEnum $state): string => match ($state) {
                        KybStatusEnum::NOT_STARTED => 'gray',
                        KybStatusEnum::PENDING => 'warning',
                        KybStatusEnum::UNDER_REVIEW => 'info',
                        KybStatusEnum::APPROVED => 'success',
                        KybStatusEnum::REJECTED => 'danger',
                    }),
                TextColumn::make('loan_proposals_count')
                    ->label('Proposals')
                    ->counts('loanProposals')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('lender_type')
                    ->options(LenderTypeEnum::class),
                SelectFilter::make('kyb_status')
                    ->options(KybStatusEnum::class),
                TernaryFilter::make('is_active')
                    ->label('Active'),
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
