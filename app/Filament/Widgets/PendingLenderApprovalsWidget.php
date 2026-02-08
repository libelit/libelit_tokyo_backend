<?php

namespace App\Filament\Widgets;

use App\Enums\KycStatusEnum;
use App\Models\LenderProfile;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingLenderApprovalsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pending Lender Approvals';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LenderProfile::query()
                    ->whereIn('kyc_status', [KycStatusEnum::PENDING, KycStatusEnum::UNDER_REVIEW])
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('lender_type')
                    ->badge(),
                TextColumn::make('kyc_status')
                    ->label('KYC')
                    ->badge()
                    ->color(fn (KycStatusEnum $state): string => match ($state) {
                        KycStatusEnum::NOT_STARTED => 'gray',
                        KycStatusEnum::PENDING => 'warning',
                        KycStatusEnum::UNDER_REVIEW => 'info',
                        KycStatusEnum::APPROVED => 'success',
                        KycStatusEnum::REJECTED => 'danger',
                    }),
                TextColumn::make('kyc_submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.lenders.view', $record))
            ->defaultSort('kyc_submitted_at', 'asc')
            ->paginated([5, 10, 25]);
    }
}
