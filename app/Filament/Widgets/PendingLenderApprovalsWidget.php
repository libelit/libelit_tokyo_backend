<?php

namespace App\Filament\Widgets;

use App\Enums\KybStatusEnum;
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
                    ->whereIn('kyb_status', [KybStatusEnum::PENDING, KybStatusEnum::UNDER_REVIEW])
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('lender_type')
                    ->badge(),
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
                TextColumn::make('kyb_submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.lenders.view', $record))
            ->defaultSort('kyb_submitted_at', 'asc')
            ->paginated([5, 10, 25]);
    }
}
