<?php

namespace App\Filament\Resources\Investors\Tables;

use App\Enums\AmlStatusEnum;
use App\Enums\InvestorTypeEnum;
use App\Enums\KycStatusEnum;
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

class InvestorsTable
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
                TextColumn::make('investor_type')
                    ->badge()
                    ->color(fn (InvestorTypeEnum $state): string => match ($state) {
                        InvestorTypeEnum::TIER_1 => 'success',
                        InvestorTypeEnum::TIER_2 => 'info',
                        InvestorTypeEnum::TIER_3 => 'gray',
                    }),
                TextColumn::make('company_name')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('kyc_status')
                    ->label('KYC')
                    ->badge()
                    ->color(fn (KycStatusEnum $state): string => match ($state) {
                        KycStatusEnum::PENDING => 'warning',
                        KycStatusEnum::UNDER_REVIEW => 'info',
                        KycStatusEnum::APPROVED => 'success',
                        KycStatusEnum::REJECTED => 'danger',
                    }),
                TextColumn::make('aml_status')
                    ->label('AML')
                    ->badge()
                    ->color(fn (AmlStatusEnum $state): string => match ($state) {
                        AmlStatusEnum::PENDING => 'warning',
                        AmlStatusEnum::CLEARED => 'success',
                        AmlStatusEnum::FLAGGED => 'danger',
                    }),
                TextColumn::make('investments_count')
                    ->label('Investments')
                    ->counts('investments')
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
                SelectFilter::make('investor_type')
                    ->options(InvestorTypeEnum::class),
                SelectFilter::make('kyc_status')
                    ->options(KycStatusEnum::class),
                SelectFilter::make('aml_status')
                    ->options(AmlStatusEnum::class),
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
