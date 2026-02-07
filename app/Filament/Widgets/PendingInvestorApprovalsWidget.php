<?php

namespace App\Filament\Widgets;

use App\Enums\AmlStatusEnum;
use App\Enums\KycStatusEnum;
use App\Models\InvestorProfile;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingInvestorApprovalsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pending Investor Approvals';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InvestorProfile::query()
                    ->where(function ($query) {
                        $query->where('kyc_status', KycStatusEnum::PENDING)
                            ->orWhere('kyc_status', KycStatusEnum::UNDER_REVIEW)
                            ->orWhere('aml_status', AmlStatusEnum::PENDING);
                    })
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('investor_type')
                    ->badge(),
                TextColumn::make('kyc_status')
                    ->label('KYC')
                    ->badge()
                    ->color(fn (KycStatusEnum $state): string => match ($state) {
                        KycStatusEnum::PENDING => 'warning',
                        KycStatusEnum::UNDER_REVIEW => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('aml_status')
                    ->label('AML')
                    ->badge()
                    ->color(fn (AmlStatusEnum $state): string => match ($state) {
                        AmlStatusEnum::PENDING => 'warning',
                        AmlStatusEnum::CLEARED => 'success',
                        AmlStatusEnum::FLAGGED => 'danger',
                    }),
                TextColumn::make('kyc_submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.investors.view', $record))
            ->defaultSort('kyc_submitted_at', 'asc')
            ->paginated([5, 10, 25]);
    }
}
